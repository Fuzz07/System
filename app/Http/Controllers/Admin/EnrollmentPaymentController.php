<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EnrollmentPayment;
use App\Models\Budget;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\SscHelper;
use App\Notifications\EnrollmentPaidNotification;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EnrollmentPaymentController extends Controller
{
    public const YEAR_LEVELS = ['1st Year', '2nd Year', '3rd Year', '4th Year'];

    public function index(Request $request)
    {
        $search = $request->input('search');
        $dept = $request->input('department');
        $year = $request->input('year_level');
        $status = $request->input('status', 'all');
        $currentSy = SscHelper::getActiveAcademicTerm();
        $currentTermKeys = SscHelper::getActiveEnrollmentTermKeys();

        $students = User::where('role', 'student')
            ->with(['enrollmentPayments' => function ($q) use ($currentTermKeys) {
                $q->whereIn('semester', $currentTermKeys)->orderByDesc('created_at');
            }]);

        if ($search) {
            $students->where(function ($q) use ($search) {
                $q->where('fullname', 'like', "%$search%")
                    ->orWhere('student_id', 'like', "%$search%");
            });
        }

        if ($dept) {
            $students->where('department', $dept);
        }

        if ($year) {
            $students->where('year_level', $year);
        }

        if ($status === 'unpaid') {
            $students->whereDoesntHave('enrollmentPayments', function ($q) use ($currentTermKeys) {
                $q->whereIn('semester', $currentTermKeys)->where('status', 'paid');
            });
        } elseif ($status === 'pending') {
            $students->whereHas('enrollmentPayments', function ($q) use ($currentTermKeys) {
                $q->whereIn('semester', $currentTermKeys)->where('status', 'pending');
            });
        } elseif ($status === 'paid') {
            $students->whereHas('enrollmentPayments', function ($q) use ($currentTermKeys) {
                $q->whereIn('semester', $currentTermKeys)->where('status', 'paid');
            });
        }

        $students = $students->orderBy('fullname')->paginate(8);

        $departments = User::where('role', 'student')->select('department')->distinct()->pluck('department');
        $years = User::where('role', 'student')->select('year_level')->distinct()->pluck('year_level');
        $distribution = $this->departmentDistribution($currentSy, $currentTermKeys);

        $portal = $this->portal($request);
        $departmentOptions = Budget::DEPARTMENTS;
        $yearLevelOptions = self::YEAR_LEVELS;

        return view('admin.enrollment_payments', compact('students', 'search', 'dept', 'year', 'status', 'departments', 'years', 'currentSy', 'distribution', 'portal', 'departmentOptions', 'yearLevelOptions'));
    }

    public function markPaid(EnrollmentPayment $payment, Request $request)
    {
        if ($payment->status === 'paid') {
            return redirect()->back()->with('info', 'Already marked paid.');
        }

        $payment->update([
            'status' => 'paid',
            'method' => 'walk_in',
            'proof_status' => $payment->proof_status ?: 'approved',
            'admin_marked_by' => Auth::id(),
            'verified_by' => Auth::id(),
            'paid_at' => now(),
        ]);

        $this->addEnrollmentBudget($payment);

        SscHelper::logActivity(Auth::id(), 'ENROLLMENT_MARK_PAID', "Marked enrollment payment #{$payment->id} as paid for user {$payment->user->email}");

        try {
            $payment->user->notify(new EnrollmentPaidNotification($payment));
        } catch (\Throwable $e) {
            // Fail silently; notification is optional
        }

        return redirect()->back()->with('success', 'Payment marked as paid, added to budget, and student notified.');
    }

    public function markPaidWalkIn(User $student)
    {
        if (! $this->recordWalkInPayment($student)) {
            return redirect()->back()->with('info', 'This student is already marked paid for ' . SscHelper::getActiveAcademicTerm() . '.');
        }

        return redirect()->back()->with('success', 'Walk-in payment recorded and student notified.');
    }

    /**
     * Add a student who has no account yet (e.g. a walk-in payer) and record their payment status.
     */
    public function storeStudent(Request $request)
    {
        $request->merge([
            'first_name' => trim((string) $request->input('first_name')),
            'middle_name' => ($middleName = trim((string) $request->input('middle_name'))) !== '' ? $middleName : null,
            'last_name' => trim((string) $request->input('last_name')),
            'student_id' => trim((string) $request->input('student_id')),
            'email' => Str::lower(trim((string) $request->input('email'))),
            'reference' => ($reference = trim((string) $request->input('reference'))) !== '' ? $reference : null,
        ]);

        $data = $request->validate([
            'first_name' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL][\pL\s.\'-]*$/u'],
            'middle_name' => ['nullable', 'string', 'max:100', 'regex:/^[\pL][\pL\s.\'-]*$/u'],
            'last_name' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL][\pL\s.\'-]*$/u'],
            'student_id' => ['required', 'regex:/^\d{4}-\d{4}$/', 'unique:users,student_id'],
            'email' => ['required', 'email:rfc', 'max:255', 'ends_with:@mcclawis.edu.ph', 'unique:users,email'],
            'department' => ['required', Rule::in(Budget::DEPARTMENTS)],
            'year_level' => ['required', Rule::in(self::YEAR_LEVELS)],
            'payment_status' => ['required', 'in:paid,unpaid'],
            'reference' => ['nullable', 'string', 'max:100'],
        ], [
            'first_name.regex' => 'First name may contain letters, spaces, periods, apostrophes, and hyphens only.',
            'middle_name.regex' => 'Middle name may contain letters, spaces, periods, apostrophes, and hyphens only.',
            'last_name.regex' => 'Last name may contain letters, spaces, periods, apostrophes, and hyphens only.',
            'student_id.regex' => 'Student ID must use the format YYYY-XXXX (for example, 2024-0001).',
            'student_id.unique' => 'A student with this Student ID already exists. Search for them in the list instead.',
            'email.ends_with' => 'Use the student\'s @mcclawis.edu.ph school email.',
            'email.unique' => 'A student with this email already exists. Search for them in the list instead.',
        ]);

        // The student sets their own password later through "Forgot Password".
        $student = User::create([
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'],
            'last_name' => $data['last_name'],
            'fullname' => trim($data['first_name'] . ' ' . ($data['middle_name'] ?? '') . ' ' . $data['last_name']),
            'student_id' => $data['student_id'],
            'email' => $data['email'],
            'department' => $data['department'],
            'year_level' => $data['year_level'],
            'password' => Str::random(40),
            'role' => 'student',
            'status' => 'active',
        ]);

        SscHelper::logActivity(Auth::id(), 'ENROLLMENT_ADD_STUDENT', "Added student {$student->email} from enrollment payments");

        $message = "{$student->fullname} was added as unpaid.";
        if ($data['payment_status'] === 'paid') {
            $this->recordWalkInPayment($student, $data['reference']);
            $message = "{$student->fullname} was added and marked as paid.";
        }

        return redirect()
            ->route($this->portal($request) . '.enrollment.payments', ['search' => $student->student_id])
            ->with('success', $message);
    }

    /**
     * Mark the student's current-term fee as paid in person and credit it to the budget.
     * Returns null when the student has already paid, so the fee is never credited twice.
     */
    protected function recordWalkInPayment(User $student, ?string $reference = null): ?EnrollmentPayment
    {
        $currentSy = SscHelper::getActiveAcademicTerm();
        $amount = config('ssc.enrollment_fee_amount', 50);

        $payment = EnrollmentPayment::where('user_id', $student->id)
            ->whereIn('semester', SscHelper::getActiveEnrollmentTermKeys())
            ->latest()
            ->first();

        if ($payment && $payment->status === 'paid') {
            return null;
        }

        if (! $payment) {
            $payment = EnrollmentPayment::create([
                'user_id' => $student->id,
                'amount' => $amount,
                'semester' => $currentSy,
                'method' => 'walk_in',
                'status' => 'paid',
                'proof_status' => 'approved',
                'reference' => $reference ?: 'WALKIN-' . strtoupper(uniqid()),
                'admin_marked_by' => Auth::id(),
                'verified_by' => Auth::id(),
                'paid_at' => now(),
            ]);
        } else {
            $payment->update([
                'amount' => $amount,
                'method' => 'walk_in',
                'status' => 'paid',
                'proof_status' => 'approved',
                'admin_marked_by' => Auth::id(),
                'verified_by' => Auth::id(),
                'paid_at' => now(),
                'reference' => $reference ?: ($payment->reference ?: 'WALKIN-' . strtoupper(uniqid())),
            ]);
        }

        $this->addEnrollmentBudget($payment);

        SscHelper::logActivity(Auth::id(), 'ENROLLMENT_MARK_PAID', "Marked walk-in enrollment payment for student {$student->email}");

        try {
            $student->notify(new EnrollmentPaidNotification($payment));
        } catch (\Throwable $e) {
            // optional
        }

        return $payment;
    }

    /** Which portal is serving this request: the page is shared by admin and treasurer. */
    protected function portal(Request $request): string
    {
        return $request->routeIs('treasurer.*') ? 'treasurer' : 'admin';
    }

    public function approveProof(EnrollmentPayment $payment)
    {
        if ($payment->status === 'paid' || ! $payment->proof_path) {
            return redirect()->back()->with('info', 'This payment cannot be approved.');
        }

        $payment->update([
            'status' => 'paid',
            'proof_status' => 'approved',
            'verified_by' => Auth::id(),
            'admin_marked_by' => Auth::id(),
            'paid_at' => now(),
        ]);

        $this->addEnrollmentBudget($payment);

        SscHelper::logActivity(Auth::id(), 'ENROLLMENT_PROOF_APPROVED', "Approved enrollment proof for payment #{$payment->id}");

        try {
            $payment->user->notify(new EnrollmentPaidNotification($payment));
        } catch (\Throwable $e) {
            // optional
        }

        return redirect()->back()->with('success', 'Payment proof approved and payment marked as paid.');
    }

    public function rejectProof(Request $request, EnrollmentPayment $payment)
    {
        $request->validate([
            'proof_notes' => 'nullable|string|max:1000',
        ]);

        $payment->update([
            'proof_status' => 'rejected',
            'proof_notes' => $request->input('proof_notes'),
        ]);

        SscHelper::logActivity(Auth::id(), 'ENROLLMENT_PROOF_REJECTED', "Rejected enrollment proof for payment #{$payment->id}");

        return redirect()->back()->with('success', 'Payment proof rejected.');
    }

    /**
     * Credit the fee to the unified Enrollment Fees budget row.
     */
    protected function addEnrollmentBudget(EnrollmentPayment $payment)
    {
        $schoolYear = $payment->semester ?: SscHelper::getActiveAcademicTerm();

        $budget = Budget::firstOrCreate(
            [
                'title'       => Budget::ENROLLMENT_TITLE_PREFIX,
                'school_year' => $schoolYear,
            ],
            [
                'department'        => 'All Departments',
                'allocated_amount'  => 0,
                'remaining_balance' => 0,
                'status'            => 'Approved',
                'created_by'        => Auth::id() ?: 1,
                'notes'             => 'Consolidated enrollment fees collection for all departments.',
            ]
        );

        $budget->allocated_amount += $payment->amount;
        $budget->remaining_balance += $payment->amount;
        $budget->status = 'Approved';
        $budget->save();

        return $budget;
    }

    /**
     * Per-department view of the enrollment collection: head counts from the
     * student roster, cash from the payments, and the matching budget row.
     *
     * @return array<string, array<string, mixed>>
     */
    protected function departmentDistribution(string $schoolYear, ?array $termKeys = null): array
    {
        $termKeys ??= [$schoolYear];
        $rows = [];

        $studentRows = User::where('role', 'student')
            ->groupBy('department')
            ->selectRaw('department, COUNT(*) as total')
            ->get();

        foreach ($studentRows as $row) {
            $key = Budget::normalizeDepartment($row->department);
            $rows[$key] ??= $this->emptyDistributionRow($key);
            $rows[$key]['students'] += (int) $row->total;
        }

        $paymentRows = EnrollmentPayment::query()
            ->join('users', 'users.id', '=', 'enrollment_payments.user_id')
            ->whereIn('enrollment_payments.semester', $termKeys)
            ->whereIn('enrollment_payments.status', ['paid', 'pending'])
            ->groupBy('users.department', 'enrollment_payments.status')
            ->selectRaw('users.department as department, enrollment_payments.status as status, COUNT(*) as total, SUM(enrollment_payments.amount) as amount')
            ->get();

        foreach ($paymentRows as $row) {
            $key = Budget::normalizeDepartment($row->department);
            $rows[$key] ??= $this->emptyDistributionRow($key);

            if ($row->status === 'paid') {
                $rows[$key]['paid'] += (int) $row->total;
                $rows[$key]['collected'] += (float) $row->amount;
            } else {
                $rows[$key]['pending'] += (int) $row->total;
            }
        }

        $budgets = Budget::enrollmentFees()->whereIn('school_year', $termKeys)->get();

        foreach ($budgets as $budget) {
            // Keeps the legacy pooled row visible until it is split or removed.
            $key = Budget::normalizeDepartment($budget->department);
            $rows[$key] ??= $this->emptyDistributionRow($key);
            $rows[$key]['allocated'] += (float) $budget->allocated_amount;
            $rows[$key]['remaining'] += (float) $budget->remaining_balance;
        }

        foreach ($rows as $key => $row) {
            $rows[$key]['unpaid'] = max(0, $row['students'] - $row['paid'] - $row['pending']);
        }

        ksort($rows);

        return $rows;
    }

    protected function emptyDistributionRow(string $department): array
    {
        return [
            'department' => $department,
            'students'   => 0,
            'paid'       => 0,
            'pending'    => 0,
            'unpaid'     => 0,
            'collected'  => 0.0,
            'allocated'  => 0.0,
            'remaining'  => 0.0,
        ];
    }
}
