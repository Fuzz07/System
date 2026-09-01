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
        $filter = $request->input('filter', 'all');
        $sort = $request->input('sort', 'dept_enrollment');

        $query = Budget::with(['creator', 'approver']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                    ->orWhere('department', 'like', "%$search%")
                    ->orWhere('school_year', 'like', "%$search%");
            });
        }

        if ($filter === 'enrollment') {
            $query->where('title', Budget::ENROLLMENT_TITLE_PREFIX);
        } elseif ($filter === 'custom') {
            $query->where('title', '!=', Budget::ENROLLMENT_TITLE_PREFIX);
        }

        if ($sort === 'amount_desc') {
            $query->orderByDesc('allocated_amount');
        } elseif ($sort === 'title_asc') {
            $query->orderBy('title', 'asc');
        } elseif ($sort === 'latest') {
            $query->orderByDesc('id');
        } else {
            // Default: Enrollment Fees consolidated row first, then all other budgets alphabetically
            $enrollmentTitle = Budget::ENROLLMENT_TITLE_PREFIX;
            $query->orderByRaw("CASE WHEN title = ? THEN 0 ELSE 1 END", [$enrollmentTitle])
                ->orderBy('department', 'asc')
                ->orderByDesc('allocated_amount')
                ->orderBy('title', 'asc');
        }

        $budgets = $query->get();

        $totalAllocated = Budget::where('status', 'Approved')->sum('allocated_amount');
        $totalEnrollmentFees = Budget::where('title', Budget::ENROLLMENT_TITLE_PREFIX)
            ->where('status', 'Approved')->sum('allocated_amount');
        $totalRemaining = Budget::where('status', 'Approved')->sum('remaining_balance');
        $departmentsCount = Budget::distinct('department')->count('department');

        return view('admin.budgets', compact(
            'budgets',
            'search',
            'filter',
            'sort',
            'totalAllocated',
            'totalEnrollmentFees',
            'totalRemaining',
            'departmentsCount'
        ));
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
        $this->ensurePending($budget);

        $budget->update(['status' => 'Approved', 'approved_by' => Auth::id()]);
        SscHelper::logActivity(Auth::id(), 'BUDGET_APPROVE', "Approved budget: {$budget->title}");
        return redirect()->route('admin.budgets')->with('success', 'Budget approved.');
    }

    public function reject(Budget $budget)
    {
        $this->ensurePending($budget);

        $budget->update(['status' => 'Rejected', 'approved_by' => Auth::id()]);
        SscHelper::logActivity(Auth::id(), 'BUDGET_REJECT', "Rejected budget: {$budget->title}");
        return redirect()->route('admin.budgets')->with('success', 'Budget rejected.');
    }

    public function destroy(Budget $budget)
    {
        if ($budget->expenses()->exists()) {
            return redirect()->route('admin.budgets')->with('danger', 'Cannot delete a budget that already has expenses.');
        }

        SscHelper::logActivity(Auth::id(), 'BUDGET_DELETE', "Deleted budget: {$budget->title}");
        $budget->delete();
        return redirect()->route('admin.budgets')->with('success', 'Budget deleted.');
    }

    private function ensurePending(Budget $budget): void
    {
        if ($budget->status !== 'Pending') {
            abort(403, 'This budget has already been reviewed.');
        }
    }
}