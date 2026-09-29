<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\SscHelper;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Proposal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProposalController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $status = $request->input('status', '');

        $query = Proposal::with(['officer', 'approver'])->withCount('comments');
        if ($search) {
            $query->where('project_title', 'like', "%$search%");
        }
        if ($status && in_array($status, ['Pending', 'Approved', 'Rejected'], true)) {
            $query->where('status', $status);
        }
        $proposals = $query->orderByDesc('created_at')->paginate(8);

        return view('admin.proposals', compact('proposals', 'search', 'status'));
    }

    public function review(Request $request, Proposal $proposal)
    {
        if ($proposal->status !== 'Pending') {
            abort(403, 'This proposal has already been reviewed.');
        }

        $request->validate([
            'action'          => 'required|in:approve,reject',
            'approved_budget' => 'nullable|numeric|min:0|max:' . Proposal::MAX_BUDGET,
            'admin_notes'     => 'nullable|string',
        ], [
            'approved_budget.max' => 'The approved budget may be at most ' . Proposal::maxBudgetLabel() . '.',
        ]);

        $action = $request->action;

        // A proposal filed before the limit may request more than it; approving
        // it as-is would slip past the cap, so ask for an amount within it.
        if ($action === 'approve' && !$request->filled('approved_budget') && (float) $proposal->requested_budget > Proposal::MAX_BUDGET) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'approved_budget' => 'This proposal requests ₱' . number_format((float) $proposal->requested_budget, 2) . ', over the ' . Proposal::maxBudgetLabel() . ' limit. Enter an approved budget of at most ' . Proposal::maxBudgetLabel() . '.',
            ]);
        }
        $proposal->status = $action === 'approve' ? 'Approved' : 'Rejected';
        $proposal->approved_by = Auth::id();
        $proposal->admin_notes = $request->admin_notes;

        if ($action === 'approve') {
            $proposal->approved_budget = $request->approved_budget ?: $proposal->requested_budget;
        }
        $proposal->save();

        $logAction = $action === 'approve' ? 'PROPOSAL_APPROVE' : 'PROPOSAL_REJECT';
        SscHelper::logActivity(Auth::id(), $logAction, ucfirst($action) . "d proposal: {$proposal->project_title}");

        if ($action === 'approve' && $proposal->project_status === 'Completed') {
            Announcement::create([
                'title'      => "Project Completed: {$proposal->project_title}",
                'content'    => "The project '{$proposal->project_title}' has been completed and approved.",
                'created_by' => Auth::id(),
                'project_id' => $proposal->id,
            ]);
        }

        return redirect()->route('admin.proposals')->with('success', "Proposal {$action}d successfully.");
    }
}