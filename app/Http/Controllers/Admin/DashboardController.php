<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\SscHelper;
use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Expense;
use App\Models\Feedback;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalBudget      = Budget::where('status', 'Approved')->sum('allocated_amount');
        $totalExpenses     = Expense::where('status', 'Approved')->sum('amount');
        $remainingBudget   = $totalBudget - $totalExpenses;
        $pendingProposals  = Proposal::where('status', 'Pending')->count();
        $pendingExpenses   = Expense::where('status', 'Pending')->count();
        $pendingFeedback   = Feedback::where('status', 'Pending')->count();
        $pendingStudents   = User::where('role', 'student')->where('status', 'inactive')->count();
        $totalUsers        = User::count();

        // Budget distribution chart
        $budgets = Budget::where('status', 'Approved')
            ->select('title', 'allocated_amount')
            ->orderByDesc('allocated_amount')
            ->get();

        // Monthly expense trend
        $monthlyExpenses = Expense::where('status', 'Approved')
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"), DB::raw('SUM(amount) as total'))
            ->groupBy('month')
            ->orderBy('month')
            ->limit(12)
            ->get();

        // Recent expenses
        $recentExpenses = Expense::with(['officer', 'budget'])
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact(
            'totalBudget', 'totalExpenses', 'remainingBudget',
            'pendingProposals', 'pendingExpenses', 'pendingFeedback', 'pendingStudents', 'totalUsers',
            'budgets', 'monthlyExpenses', 'recentExpenses'
        ));
    }
}
