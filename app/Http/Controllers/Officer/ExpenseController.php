<?php

namespace App\Http\Controllers\Officer;

use App\Helpers\SscHelper;
use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Expense;
use App\Support\UploadValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    public function index()
    {
        $budgets = Budget::where('status', 'Approved')->orderBy('title')->get();
        $expenses = Expense::with('budget')
            ->where('officer_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();
        return view('officer.expenses', compact('budgets', 'expenses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'budget_id'     => ['required', Rule::exists('budgets', 'id')->where(fn ($query) => $query->where('status', 'Approved'))],
            'expense_title' => 'required|string|max:255',
            'amount'        => 'required|numeric|min:1|max:100000',
            'description'   => 'nullable|string',
            'receipt'       => UploadValidation::optionalFile(),
        ]);

        $budget = Budget::whereKey($request->budget_id)
            ->where('status', 'Approved')
            ->firstOrFail();

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('receipts', 'public');
        }

        Expense::create([
            'budget_id'     => $budget->id,
            'officer_id'    => Auth::id(),
            'expense_title' => $request->expense_title,
            'amount'        => $request->amount,
            'receipt'       => $receiptPath,
            'description'   => $request->description,
        ]);

        SscHelper::logActivity(Auth::id(), 'EXPENSE_SUBMIT', "Filed expense: {$request->expense_title} ({$request->amount})");
        return redirect()->route('officer.expenses')->with('success', 'Expense submitted for approval.');
    }
}