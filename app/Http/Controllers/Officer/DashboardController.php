<?php

namespace App\Http\Controllers\Officer;

use App\Helpers\SscHelper;
use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Expense;
use App\Models\Proposal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $uid = Auth::id();
        $myProposals    = Proposal::where('officer_id', $uid)->count();
        $myExpenses     = Expense::where('officer_id', $uid)->count();
        $approvedBudget = Proposal::where('officer_id', $uid)->where('status', 'Approved')->sum('approved_budget');
        $pendingItems   = Proposal::where('officer_id', $uid)->where('status', 'Pending')->count()
                        + Expense::where('officer_id', $uid)->where('status', 'Pending')->count();

        $recentExpenses = Expense::with('budget')
            ->where('officer_id', $uid)
            ->orderByDesc('created_at')
            ->limit(5)->get();

        return view('officer.dashboard', compact(
            'myProposals', 'myExpenses', 'approvedBudget', 'pendingItems', 'recentExpenses'
        ));
    }
}
