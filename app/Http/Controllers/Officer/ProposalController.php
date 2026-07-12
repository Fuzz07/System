<?php

namespace App\Http\Controllers\Officer;

use App\Helpers\SscHelper;
use App\Http\Controllers\Controller;
use App\Models\Proposal;
use App\Support\UploadValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProposalController extends Controller
{
    public function index()
    {
        $proposals = Proposal::with('approver')
            ->withCount('comments')
            ->where('officer_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();
        return view('officer.proposals', compact('proposals'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_title'    => 'required|string|max:255',
            'requested_budget' => 'required|numeric|min:1',
            'description'      => 'required|string',
        ]);

        Proposal::create([
            'officer_id'       => Auth::id(),
            'project_title'    => $request->project_title,
            'requested_budget' => $request->requested_budget,
            'description'      => $request->description,
        ]);

        SscHelper::logActivity(Auth::id(), 'PROPOSAL_SUBMIT', "Submitted proposal: {$request->project_title}");
        return redirect()->route('officer.proposals')->with('success', 'Proposal submitted successfully!');
    }

    public function update(Request $request, Proposal $proposal)
    {
        if ($proposal->officer_id !== Auth::id() || $proposal->status !== 'Pending') {
            return redirect()->route('officer.proposals')->with('danger', 'Cannot edit this proposal.');
        }

        $request->validate([
            'project_title'    => 'required|string|max:255',
            'requested_budget' => 'required|numeric|min:1',
            'description'      => 'required|string',
        ]);

        $proposal->update($request->only('project_title', 'requested_budget', 'description'));
        SscHelper::logActivity(Auth::id(), 'PROPOSAL_UPDATE', "Updated proposal: {$request->project_title}");
        return redirect()->route('officer.proposals')->with('success', 'Proposal updated successfully!');
    }

    public function complete(Request $request, Proposal $proposal)
    {
        if ($proposal->officer_id !== Auth::id()) abort(403);

        $request->validate([
            'receipt' => UploadValidation::requiredFile(),
        ]);

        $receiptPath = $request->file('receipt')->store('receipts', 'public');

        $proposal->update([
            'project_status'   => 'Completed',
            'completion_proof' => $receiptPath,
        ]);

        SscHelper::logActivity(Auth::id(), 'PROJECT_COMPLETE', "Project ID {$proposal->id} marked as completed.");
        return redirect()->route('officer.proposals')->with('success', 'Project marked as completed!');
    }
}
