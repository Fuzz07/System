<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\SscHelper;
use App\Http\Controllers\Controller;
use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $query = Budget::with(['creator', 'approver']);
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%$search%")->orWhere('department', 'like', "%$search%");
            });
        }
        $budgets = $query->orderByDesc('created_at')->get();
        return view('admin.budgets', compact('budgets', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'            => 'required|string|max:255',
            'department'       => 'required|string|max:100',
            'allocated_amount' => 'required|numeric|min:1',
            'school_year'      => 'required|string',
            'notes'            => 'nullable|string',
        ]);

        Budget::create([
            'title'             => $request->title,
            'department'        => $request->department,
            'allocated_amount'  => $request->allocated_amount,
            'remaining_balance' => $request->allocated_amount,
            'school_year'       => $request->school_year,
            'created_by'        => Auth::id(),
            'notes'             => $request->notes,
        ]);

        SscHelper::logActivity(Auth::id(), 'BUDGET_CREATE', "Created budget: {$request->title}");
        return redirect()->route('admin.budgets')->with('success', 'Budget created successfully.');
    }

    public function approve(Budget $budget)
    {
        $budget->update(['status' => 'Approved', 'approved_by' => Auth::id()]);
        SscHelper::logActivity(Auth::id(), 'BUDGET_APPROVE', "Approved budget: {$budget->title}");
        return redirect()->route('admin.budgets')->with('success', 'Budget approved.');
    }

    public function reject(Budget $budget)
    {
        $budget->update(['status' => 'Rejected', 'approved_by' => Auth::id()]);
        SscHelper::logActivity(Auth::id(), 'BUDGET_REJECT', "Rejected budget: {$budget->title}");
        return redirect()->route('admin.budgets')->with('success', 'Budget rejected.');
    }

    public function destroy(Budget $budget)
    {
        SscHelper::logActivity(Auth::id(), 'BUDGET_DELETE', "Deleted budget: {$budget->title}");
        $budget->delete();
        return redirect()->route('admin.budgets')->with('success', 'Budget deleted.');
    }
}
