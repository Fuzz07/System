<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\SscHelper;
use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        if ($status && in_array($status, ['Pending', 'Approved', 'Rejected'])) {
            $query->where('status', $status);
        }
        $expenses = $query->orderByDesc('created_at')->get();

        return view('admin.expenses', compact('expenses', 'search', 'status'));
    }

    public function review(Request $request, Expense $expense)
    {
        $request->validate([
            'action'      => 'required|in:approve,reject',
            'admin_notes' => 'nullable|string',
        ]);

        $action = $request->action;

        if ($action === 'approve') {
            $budget = Budget::find($expense->budget_id);
            if ($budget && $budget->remaining_balance >= $expense->amount) {
                $budget->decrement('remaining_balance', $expense->amount);
                $expense->update([
                    'status'      => 'Approved',
                    'approved_by' => Auth::id(),
                    'admin_notes' => $request->admin_notes,
                ]);
                SscHelper::logActivity(Auth::id(), 'EXPENSE_APPROVE', "Approved expense: {$expense->expense_title}");
            } else {
                return redirect()->route('admin.expenses')->with('danger', 'Insufficient budget balance.');
            }
        } else {
            $expense->update([
                'status'      => 'Rejected',
                'approved_by' => Auth::id(),
                'admin_notes' => $request->admin_notes,
            ]);
            SscHelper::logActivity(Auth::id(), 'EXPENSE_REJECT', "Rejected expense: {$expense->expense_title}");
        }

        return redirect()->route('admin.expenses')->with('success', "Expense {$action}d successfully.");
    }
}
