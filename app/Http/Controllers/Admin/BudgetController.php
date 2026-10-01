<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\SscHelper;
use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\SchoolYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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
            $query->orderByRaw('CASE WHEN title = ? THEN 0 ELSE 1 END', [$enrollmentTitle])
                ->orderBy('department', 'asc')
                ->orderByDesc('allocated_amount')
                ->orderBy('title', 'asc');
        }

        // The table pages through the budgets; the print report needs them all.
        $budgetPage = (clone $query)->paginate(10);
        $budgets = $query->get();

        $totalAllocated = Budget::where('status', 'Approved')->sum('allocated_amount');
        $totalEnrollmentFees = Budget::where('title', Budget::ENROLLMENT_TITLE_PREFIX)
            ->where('status', 'Approved')->sum('allocated_amount');
        $totalRemaining = Budget::where('status', 'Approved')->sum('remaining_balance');
        $departmentsCount = Budget::distinct('department')->count('department');
        $departmentOptions = Budget::DEPARTMENTS;
        $schoolYearOptions = $this->schoolYearOptions();
        $defaultSchoolYear = SchoolYear::where('is_active', true)->value('label')
            ?: now()->year.'-'.(now()->year + 1);

        return view('admin.budgets', compact(
            'budgetPage',
            'budgets',
            'search',
            'filter',
            'sort',
            'totalAllocated',
            'totalEnrollmentFees',
            'totalRemaining',
            'departmentsCount',
            'departmentOptions',
            'schoolYearOptions',
            'defaultSchoolYear'
        ));
    }

    public function store(Request $request)
    {
        // Runs of spaces in a title collapse to one, so "Sports  Fund" and
        // "Sports Fund" are the same fund to the duplicate check below.
        if (is_string($request->input('title'))) {
            $request->merge(['title' => trim(preg_replace('/\s+/u', ' ', $request->input('title')))]);
        }

        $validated = $request->validate([
            'title' => [
                'bail',
                'required',
                'string',
                'min:3',
                'max:' . Budget::TITLE_MAX,
                'regex:/^[\pL\pN][\pL\pN .,&()\'\/-]*$/u',
                'regex:/\pL/u',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    // Enrollment payments find the contribution fee fund by this
                    // title, so a manual fund named like it would be credited too.
                    if (str_starts_with(mb_strtolower($value), mb_strtolower(Budget::ENROLLMENT_TITLE_PREFIX))) {
                        $fail('"' . Budget::ENROLLMENT_TITLE_PREFIX . '" is reserved for the fund that collects student contributions automatically.');
                    }
                },
                Rule::unique('budgets', 'title')->where(fn ($query) => $query
                    ->where('department', $request->input('department'))
                    ->where('school_year', $request->input('school_year'))
                    ->where('status', '!=', 'Rejected')),
            ],
            'department' => ['required', Rule::in([Budget::ALL_DEPARTMENTS, ...Budget::DEPARTMENTS])],
            'allocated_amount' => ['bail', 'required', 'regex:/^[1-9]\d*(?:\.\d{1,2})?$/', 'numeric', 'min:1', 'max:' . Budget::MAX_ALLOCATION],
            'school_year' => [
                'bail',
                'required',
                'regex:/^\d{4}-\d{4}$/',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    [$startYear, $endYear] = array_map('intval', explode('-', (string) $value));

                    if ($endYear !== $startYear + 1) {
                        $fail('The school year must contain consecutive years, such as 2026-2027.');
                    }
                },
                Rule::in($this->schoolYearOptions()),
            ],
            'notes' => 'nullable|string|max:' . Budget::NOTES_MAX,
        ], [
            'title.min' => 'The budget title must be at least 3 characters.',
            'title.max' => 'The budget title may be at most ' . Budget::TITLE_MAX . ' characters.',
            'title.regex' => 'The budget title must start with a letter or number, contain at least one letter, and use only letters, numbers, spaces and . , & ( ) \' / -',
            'title.unique' => 'A fund with this title already exists for this department and school year.',
            'department.in' => 'Please select All Departments or one of the five available departments.',
            'allocated_amount.regex' => 'The allocated amount must start with a digit from 1 to 9 and have no more than two decimal places.',
            'allocated_amount.max' => 'The allocated amount may be at most ' . Budget::maxAllocationLabel() . '.',
            'school_year.regex' => 'Please select a valid school year.',
            'school_year.in' => 'Please select one of the listed school years.',
            'notes.max' => 'Notes may be at most ' . Budget::NOTES_MAX . ' characters.',
        ]);

        Budget::create([
            'title' => $validated['title'],
            'department' => $validated['department'],
            'allocated_amount' => $validated['allocated_amount'],
            'remaining_balance' => $validated['allocated_amount'],
            'school_year' => $validated['school_year'],
            'created_by' => Auth::id(),
            'notes' => $validated['notes'] ?? null,
            // The admin is the only one who approves budgets, so funds they add
            // themselves are approved as they are added. Approve and Reject stay
            // for budgets that arrive pending from elsewhere.
            'status' => 'Approved',
            'approved_by' => Auth::id(),
        ]);

        SscHelper::logActivity(Auth::id(), 'BUDGET_CREATE', "Created budget: {$validated['title']}");

        return redirect()->route('admin.budgets')->with('success', 'Funds added successfully.');
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

    /**
     * Build a useful range of consecutive academic years and retain valid
     * school years that administrators have already configured in Settings.
     *
     * @return array<int, string>
     */
    private function schoolYearOptions(): array
    {
        $currentYear = now()->year;
        $options = SchoolYear::pluck('label')->all();

        foreach (range($currentYear - 10, $currentYear + 5) as $startYear) {
            $options[] = $startYear.'-'.($startYear + 1);
        }

        $options = array_values(array_unique(array_filter(
            $options,
            static function (mixed $label): bool {
                if (! is_string($label) || ! preg_match('/^(\d{4})-(\d{4})$/', $label, $matches)) {
                    return false;
                }

                return (int) $matches[2] === (int) $matches[1] + 1;
            }
        )));

        rsort($options, SORT_STRING);

        return $options;
    }
}
