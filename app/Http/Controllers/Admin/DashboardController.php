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

        // Fetch any pending device login approvals
        $pendingApprovals = [];
        $userPendingKey = "admin_pending_approvals_" . Auth::id();
        $pendingList = \Illuminate\Support\Facades\Cache::get($userPendingKey, []);
        foreach ($pendingList as $approvalId) {
            $requestData = \Illuminate\Support\Facades\Cache::get("admin_login_approval_{$approvalId}");
            if ($requestData && $requestData['status'] === 'pending') {
                $pendingApprovals[] = $requestData;
            }
        }

        return view('admin.dashboard', compact(
            'totalBudget', 'totalExpenses', 'remainingBudget',
            'pendingProposals', 'pendingExpenses', 'pendingFeedback', 'pendingStudents', 'totalUsers',
            'budgets', 'monthlyExpenses', 'recentExpenses', 'pendingApprovals'
        ));
    }

    public function approveLoginRequest($approvalId)
    {
        $requestData = \Illuminate\Support\Facades\Cache::get("admin_login_approval_{$approvalId}");
        if ($requestData && $requestData['user_id'] === Auth::id()) {
            $requestData['status'] = 'approved';
            \Illuminate\Support\Facades\Cache::put("admin_login_approval_{$approvalId}", $requestData, now()->addMinutes(5));

            // Remove from admin pending approvals list
            $userPendingKey = "admin_pending_approvals_" . Auth::id();
            $pendingList = \Illuminate\Support\Facades\Cache::get($userPendingKey, []);
            $pendingList = array_diff($pendingList, [$approvalId]);
            \Illuminate\Support\Facades\Cache::put($userPendingKey, $pendingList, now()->addMinutes(5));

            SscHelper::logActivity(Auth::id(), 'DEVICE_APPROVED', "Approved login request {$approvalId} for unregistered device");

            return back()->with('success', "Login request approved! The secure verification code is: {$requestData['otp']}. Provide this code to the new device to log in.");
        }

        return back()->with('danger', 'Login request not found or unauthorized.');
    }

    public function rejectLoginRequest($approvalId)
    {
        $requestData = \Illuminate\Support\Facades\Cache::get("admin_login_approval_{$approvalId}");
        if ($requestData && $requestData['user_id'] === Auth::id()) {
            $requestData['status'] = 'rejected';
            \Illuminate\Support\Facades\Cache::put("admin_login_approval_{$approvalId}", $requestData, now()->addMinutes(5));

            // Remove from admin pending approvals list
            $userPendingKey = "admin_pending_approvals_" . Auth::id();
            $pendingList = \Illuminate\Support\Facades\Cache::get($userPendingKey, []);
            $pendingList = array_diff($pendingList, [$approvalId]);
            \Illuminate\Support\Facades\Cache::put($userPendingKey, $pendingList, now()->addMinutes(5));

            SscHelper::logActivity(Auth::id(), 'DEVICE_REJECTED', "Rejected login request {$approvalId} for unregistered device");

            return back()->with('success', 'Login request declined.');
        }

        return back()->with('danger', 'Login request not found or unauthorized.');
    }
}
