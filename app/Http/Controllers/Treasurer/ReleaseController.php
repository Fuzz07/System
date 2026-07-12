<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Helpers\SscHelper;
use App\Models\Announcement;
use App\Models\BudgetRelease;
use App\Models\Proposal;
use App\Support\UploadValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReleaseController extends Controller
{
    public function index(Request $request)
    {
        $filterSearch = trim($request->query('search', ''));
        $selectedPid = (int)$request->query('proposal_id', 0);

        // Fetch all approved proposals with release summary
        $proposalsQuery = Proposal::where('status', 'Approved')
            ->with('officer')
            ->select('proposals.*')
            ->selectSub(function ($query) {
                $query->selectRaw('COALESCE(SUM(amount_released), 0)')
                    ->from('budget_releases')
                    ->whereColumn('proposal_id', 'proposals.id');
            }, 'total_released')
            ->selectSub(function ($query) {
                $query->selectRaw('COUNT(id)')
                    ->from('budget_releases')
                    ->whereColumn('proposal_id', 'proposals.id');
            }, 'release_count');

        if ($filterSearch !== '') {
            $proposalsQuery->where(function($q) use ($filterSearch) {
                $q->where('project_title', 'like', "%{$filterSearch}%")
                  ->orWhereHas('officer', function($uq) use ($filterSearch) {
                      $uq->where('fullname', 'like', "%{$filterSearch}%");
                  });
            });
        }

        $proposals = $proposalsQuery->orderBy('created_at', 'desc')->get();

        // All budget releases history
        $allReleases = BudgetRelease::with(['proposal.officer', 'treasurer'])
            ->orderBy('created_at', 'desc')
            ->get();

        // If a specific proposal is pre-selected
        $selectedProposal = null;
        if ($selectedPid > 0) {
            $selectedProposal = $proposals->firstWhere('id', $selectedPid);
        }

        return view('treasurer.release', compact(
            'filterSearch',
            'proposals',
            'allReleases',
            'selectedProposal',
            'selectedPid'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'proposal_id'    => 'required|exists:proposals,id',
            'amount_released'=> 'required|numeric|min:0.01',
            'release_method' => 'required|string|max:255',
            'reference_no'   => 'nullable|string|max:255',
            'release_status' => 'required|in:Released,Partial',
            'notes'          => 'nullable|string',
            'receipt'        => UploadValidation::optionalFile(),
        ]);

        $pid = (int)$request->proposal_id;
        $proposal = Proposal::where('id', $pid)->where('status', 'Approved')->firstOrFail();

        // Calculate maximum releasable amount
        $totalAlreadyReleased = (float)BudgetRelease::where('proposal_id', $pid)->sum('amount_released');
        $approvedBudget = (float)$proposal->approved_budget;
        $maxReleasable = $approvedBudget - $totalAlreadyReleased;

        $amtReleased = (float)$request->amount_released;

        if ($amtReleased > $maxReleasable + 0.01) {
            return redirect()->back()
                ->with('danger', "Cannot release " . SscHelper::formatCurrency($amtReleased) . ". Only " . SscHelper::formatCurrency($maxReleasable) . " remaining to release.")
                ->withInput();
        }

        // Handle receipt upload
        $receiptFile = null;
        if ($request->hasFile('receipt')) {
            $receiptFile = $request->file('receipt')->store('releases', 'public');
        }

        BudgetRelease::create([
            'proposal_id'     => $pid,
            'released_by'     => Auth::id(),
            'amount_released' => $amtReleased,
            'release_method'  => $request->release_method,
            'reference_no'    => $request->reference_no,
            'receipt_file'    => $receiptFile,
            'notes'           => $request->notes,
            'release_status'  => $request->release_status,
        ]);

        // If release status is "Released" (Final), it means fully released.
        // We can log this
        $formattedAmt = SscHelper::formatCurrency($amtReleased);
        SscHelper::logActivity(
            Auth::id(),
            'BUDGET_RELEASE',
            "Released {$formattedAmt} for Proposal ID {$pid} via {$request->release_method}"
        );

        return redirect()->route('treasurer.release')->with('success', "Budget of {$formattedAmt} released successfully for \"{$proposal->project_title}\".");
    }

    public function reports()
    {
        $sy = SscHelper::getActiveSchoolYear();

        $totalReleased = BudgetRelease::sum('amount_released');
        $countReleases = BudgetRelease::count();
        $approvedBudget = Proposal::where('status', 'Approved')->sum('approved_budget');

        $byMethod = BudgetRelease::select('release_method', DB::raw('COUNT(*) as cnt'), DB::raw('SUM(amount_released) as total'))
            ->groupBy('release_method')
            ->orderByDesc('total')
            ->get();

        $releases = BudgetRelease::with(['proposal.officer', 'treasurer'])
            ->orderBy('created_at', 'desc')
            ->get();

        $proposalSummary = Proposal::where('status', 'Approved')
            ->with('officer')
            ->select('proposals.*')
            ->selectSub(function ($query) {
                $query->selectRaw('COALESCE(SUM(amount_released), 0)')
                    ->from('budget_releases')
                    ->whereColumn('proposal_id', 'proposals.id');
            }, 'total_released')
            ->selectSub(function ($query) {
                $query->selectRaw('COUNT(id)')
                    ->from('budget_releases')
                    ->whereColumn('proposal_id', 'proposals.id');
            }, 'release_count')
            ->orderByDesc('total_released')
            ->get();

        return view('treasurer.reports', compact(
            'sy',
            'totalReleased',
            'countReleases',
            'approvedBudget',
            'byMethod',
            'releases',
            'proposalSummary'
        ));
    }

    public function announcements()
    {
        $announcements = Announcement::with(['author', 'proposal'])->orderByDesc('created_at')->get();
        return view('treasurer.announcements', compact('announcements'));
    }
}
