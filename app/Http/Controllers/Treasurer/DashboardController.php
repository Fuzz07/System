<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Helpers\SscHelper;
use App\Models\Budget;
use App\Models\BudgetRelease;
use App\Models\Proposal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $sy = SscHelper::getActiveSchoolYear();

        // Total allocated budget
        $totalBudget = Budget::where('status', 'Approved')->sum('allocated_amount');

        // Total released amount
        $totalReleased = BudgetRelease::whereIn('release_status', ['Released', 'Partial'])->sum('amount_released');

        // Count approved proposals pending release (never released or only partially released)
        $pendingRelease = Proposal::where('status', 'Approved')
            ->whereNotIn('id', function($query) {
                $query->select('proposal_id')
                      ->from('budget_releases')
                      ->whereIn('release_status', ['Released', 'Partial']);
            })
            ->count();

        // Count fully released
        $releasedCount = BudgetRelease::where('release_status', 'Released')->count();

        // Recent releases
        $recentReleases = BudgetRelease::with(['proposal.officer', 'treasurer'])
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        // Approved proposals awaiting release (total released < approved budget)
        $awaitingRelease = Proposal::where('status', 'Approved')
            ->with('officer')
            ->select('proposals.*')
            ->selectSub(function ($query) {
                $query->selectRaw('COALESCE(SUM(amount_released), 0)')
                    ->from('budget_releases')
                    ->whereColumn('proposal_id', 'proposals.id');
            }, 'total_released')
            ->havingRaw('total_released < approved_budget')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('treasurer.dashboard', compact(
            'sy',
            'totalBudget',
            'totalReleased',
            'pendingRelease',
            'releasedCount',
            'recentReleases',
            'awaitingRelease'
        ));
    }
}
