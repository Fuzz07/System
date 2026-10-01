<?php

namespace App\Http\Controllers\Officer;

use App\Helpers\SscHelper;
use App\Http\Controllers\Controller;
use App\Models\Proposal;
use App\Support\UploadValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProposalController extends Controller
{
    public function index()
    {
        $proposals = Proposal::with([
                'approver',
                'comments' => fn ($query) => $query->with('user')->orderByDesc('created_at'),
            ])
            ->withCount('comments')
            ->withSum('releases', 'amount_released')
            ->where('officer_id', Auth::id())
            ->orderByDesc('created_at')
            ->paginate(8);
        return view('officer.proposals', compact('proposals'));
    }

    public function store(Request $request)
    {
        Proposal::create(['officer_id' => Auth::id()] + $this->validatedProposal($request));

        SscHelper::logActivity(Auth::id(), 'PROPOSAL_SUBMIT', "Submitted proposal: {$request->project_title}");
        return redirect()->route('officer.proposals')->with('success', 'Proposal submitted successfully!');
    }

    public function update(Request $request, Proposal $proposal)
    {
        if ((int) $proposal->officer_id !== (int) Auth::id() || !$proposal->isEditableByOfficer()) {
            abort(403, 'Cannot edit this proposal.');
        }

        $data = $this->validatedProposal($request);

        // Saving a rejected proposal sends it back for approval. The admin's
        // rejection note stays so the next review can see what was asked for.
        if ($proposal->status === 'Rejected') {
            $proposal->update($data + [
                'status' => 'Pending',
                'approved_by' => null,
                'approved_budget' => null,
                'resubmitted_at' => now(),
            ]);
            SscHelper::logActivity(Auth::id(), 'PROPOSAL_RESUBMIT', "Revised and resubmitted rejected proposal: {$proposal->project_title}");
            return redirect()->route('officer.proposals')->with('success', 'Proposal revised and resubmitted for approval.');
        }

        $proposal->update($data);
        SscHelper::logActivity(Auth::id(), 'PROPOSAL_UPDATE', "Updated proposal: {$request->project_title}");
        return redirect()->route('officer.proposals')->with('success', 'Proposal updated successfully!');
    }

    public function destroy(Proposal $proposal)
    {
        // Only proposals that were never approved, the same ones the officer may
        // edit. An approved one carries budget releases, liquidations and
        // announcements, which deleting it would take down with it.
        if ((int) $proposal->officer_id !== (int) Auth::id() || !$proposal->isEditableByOfficer()) {
            abort(403, 'Cannot delete this proposal.');
        }

        DB::transaction(function () use ($proposal) {
            // proposal_comments has no foreign key to cascade the delete.
            $proposal->comments()->delete();
            $proposal->delete();
        });

        SscHelper::logActivity(Auth::id(), 'PROPOSAL_DELETE', "Deleted proposal: {$proposal->project_title}");
        return redirect()->route('officer.proposals')->with('success', 'Proposal deleted.');
    }

    /**
     * Validates the proposal form. When estimated expense items are given,
     * the requested budget is their combined total.
     */
    private function validatedProposal(Request $request): array
    {
        // Ignore rows the officer added but left completely empty.
        $rows = array_values(array_filter((array) $request->input('budget_items', []), function ($row) {
            return is_array($row) && collect($row)->only(['description', 'qty', 'unit_cost'])
                ->contains(fn ($value) => trim((string) $value) !== '');
        }));
        $request->merge(['budget_items' => $rows]);

        $request->validate([
            'project_title'              => 'required|string|max:255',
            'requested_budget'           => 'required_without:budget_items|nullable|numeric|min:1|max:' . Proposal::MAX_BUDGET,
            'description'                => 'required|string',
            'budget_items'               => 'array|max:50',
            'budget_items.*.description' => 'required|string|max:255',
            'budget_items.*.qty'         => 'required|integer|min:1|max:100000',
            'budget_items.*.unit_cost'   => 'required|numeric|min:0|max:' . Proposal::MAX_BUDGET,
        ], [
            'requested_budget.max'                 => 'A proposal may request at most ' . Proposal::maxBudgetLabel() . '.',
            'budget_items.*.description.required' => 'Each expense item needs a description.',
            'budget_items.*.qty.required'          => 'Each expense item needs a quantity.',
            'budget_items.*.unit_cost.required'    => 'Each expense item needs a unit cost.',
            'budget_items.*.unit_cost.max'         => 'A unit cost may be at most ' . Proposal::maxBudgetLabel() . '.',
        ]);

        $data = $request->only('project_title', 'description');

        if (empty($rows)) {
            $data['requested_budget'] = $request->requested_budget;
            $data['budget_items'] = null;
            return $data;
        }

        $items = array_map(fn ($row) => [
            'description' => trim($row['description']),
            'qty'         => (int) $row['qty'],
            'unit_cost'   => round((float) $row['unit_cost'], 2),
        ], $rows);
        $total = round(array_sum(array_map(fn ($item) => $item['qty'] * $item['unit_cost'], $items)), 2);

        if ($total < 1) {
            throw ValidationException::withMessages([
                'budget_items' => 'The estimated expenses must total at least ₱1.00.',
            ]);
        }

        if ($total > Proposal::MAX_BUDGET) {
            throw ValidationException::withMessages([
                'budget_items' => 'The estimated expenses total ₱' . number_format($total, 2) . ', which is over the ' . Proposal::maxBudgetLabel() . ' limit per proposal.',
            ]);
        }

        $data['requested_budget'] = $total;
        $data['budget_items'] = json_encode($items, JSON_UNESCAPED_UNICODE);
        return $data;
    }

    public function complete(Request $request, Proposal $proposal)
    {
        if ((int) $proposal->officer_id !== (int) Auth::id() || $proposal->status !== 'Approved' || $proposal->project_status !== 'Ongoing') {
            abort(403, 'Cannot complete this proposal.');
        }

        if (!$proposal->isBudgetFullyReleased()) {
            return redirect()->route('officer.proposals')->with('danger', "Wait for the treasurer to release the full budget for \"{$proposal->project_title}\" before marking it completed.");
        }

        $request->validate([
            'receipt' => UploadValidation::requiredFile(),
        ]);

        try {
            $receiptPath = SscHelper::uploadToCloudinary($request->file('receipt'), 'receipts');
        } catch (\Exception $e) {
            \Log::warning('Cloudinary upload failed for proposal completion receipt, falling back to local public disk: ' . $e->getMessage());
            $receiptPath = $request->file('receipt')->store('receipts', 'public');
        }

        $proposal->update([
            'project_status'   => 'Completed',
            'completion_proof' => $receiptPath,
        ]);

        SscHelper::logActivity(Auth::id(), 'PROJECT_COMPLETE', "Project ID {$proposal->id} marked as completed.");
        return redirect()->route('officer.proposals')->with('success', 'Project marked as completed!');
    }
}