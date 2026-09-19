<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Expense;
use App\Helpers\SscHelper;
use Illuminate\Http\Request;

class BudgetApiController extends Controller
{
    /**
     * Get budgets summary and list.
     */
    public function index(Request $request)
    {
        $schoolYear = $request->input('school_year', SscHelper::getActiveAcademicTerm());

        $query = Budget::query();
        if ($schoolYear && $schoolYear !== 'N/A') {
            $query->where('school_year', $schoolYear);
        }

        $budgets = $query->orderByDesc('id')->get();

        $totalAllocated = $budgets->sum('allocated_amount');
        $totalRemaining = $budgets->sum('remaining_balance');
        $totalSpent = $totalAllocated - $totalRemaining;

        return response()->json([
            'success' => true,
            'data'    => [
                'school_year'     => $schoolYear,
                'summary'         => [
                    'total_allocated' => (float) $totalAllocated,
                    'total_spent'     => (float) $totalSpent,
                    'total_remaining' => (float) $totalRemaining,
                ],
                'budgets'         => $budgets->map(function ($b) {
                    return [
                        'id'                => $b->id,
                        'title'             => $b->title,
                        'department'        => $b->department,
                        'allocated_amount'  => (float) $b->allocated_amount,
                        'remaining_balance' => (float) $b->remaining_balance,
                        'spent_amount'      => (float) ($b->allocated_amount - $b->remaining_balance),
                        'school_year'       => $b->school_year,
                        'status'            => $b->status,
                        'created_at'        => $b->created_at?->toIso8601String(),
                    ];
                }),
            ],
        ]);
    }

    /**
     * Get approved transparency expenses list.
     */
    public function expenses(Request $request)
    {
        $query = Expense::with(['budget:id,title,department', 'officer:id,fullname,position'])
            ->where('status', 'Approved')
            ->orderByDesc('created_at');

        $expenses = $query->paginate($request->input('per_page', 20));

        $expenses->getCollection()->transform(function ($e) {
            return [
                'id'            => $e->id,
                'expense_title' => $e->expense_title,
                'amount'        => (float) $e->amount,
                'description'   => $e->description,
                'receipt_url'   => $e->receipt ? SscHelper::getUploadUrl($e->receipt) : null,
                'status'        => $e->status,
                'created_at'    => $e->created_at?->toIso8601String(),
                'budget'        => $e->budget ? [
                    'id'         => $e->budget->id,
                    'title'      => $e->budget->title,
                    'department' => $e->budget->department,
                ] : null,
                'officer'       => $e->officer ? [
                    'id'       => $e->officer->id,
                    'fullname' => $e->officer->fullname,
                    'position' => $e->officer->position,
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $expenses,
        ]);
    }
}
