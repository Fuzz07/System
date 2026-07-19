<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\SscHelper;
use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $status = $request->input('status', '');

        $query = Expense::with(['officer', 'budget', 'approver']);
        if ($search) {
            $query->where('expense_title', 'like', "%$search%");
        }
        if ($status && in_array($status, ['Pending', 'Approved', 'Rejected'], true)) {
            $query->where('status', $status);
        }
        $expenses = $query->orderByDesc('created_at')->get();

        return view('admin.expenses', compact('expenses', 'search', 'status'));
    }

    public function review(Request $request, Expense $expense)
    {
        if ($expense->status !== 'Pending') {
            abort(403, 'This expense has already been reviewed.');
        }

        $request->validate([
            'action'      => 'required|in:approve,reject',
            'admin_notes' => 'nullable|string',
        ]);

        $action = $request->action;
        $errorMessage = null;

        DB::transaction(function () use ($request, $expense, $action, &$errorMessage) {
            $lockedExpense = Expense::whereKey($expense->id)->lockForUpdate()->firstOrFail();

            if ($lockedExpense->status !== 'Pending') {
                abort(403, 'This expense has already been reviewed.');
            }

            if ($action === 'approve') {
                $budget = Budget::whereKey($lockedExpense->budget_id)->lockForUpdate()->first();
                if (!$budget || $budget->status !== 'Approved' || $budget->remaining_balance < $lockedExpense->amount) {
                    $errorMessage = 'Insufficient or unavailable budget balance.';
                    return;
                }

                $budget->decrement('remaining_balance', $lockedExpense->amount);
                $lockedExpense->update([
                    'status'      => 'Approved',
                    'approved_by' => Auth::id(),
                    'admin_notes' => $request->admin_notes,
                ]);
                SscHelper::logActivity(Auth::id(), 'EXPENSE_APPROVE', "Approved expense: {$lockedExpense->expense_title}");
            } else {
                $lockedExpense->update([
                    'status'      => 'Rejected',
                    'approved_by' => Auth::id(),
                    'admin_notes' => $request->admin_notes,
                ]);
                SscHelper::logActivity(Auth::id(), 'EXPENSE_REJECT', "Rejected expense: {$lockedExpense->expense_title}");
            }
        });

        if ($errorMessage) {
            return redirect()->route('admin.expenses')->with('danger', $errorMessage);
        }

        return redirect()->route('admin.expenses')->with('success', "Expense {$action}d successfully.");
    }
}