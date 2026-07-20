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

class EnrollmentPaymentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $dept = $request->input('department');
        $year = $request->input('year_level');
        $status = $request->input('status', 'all');
        $currentSy = config('ssc.current_school_year');

        $students = User::where('role', 'student')
            ->with(['enrollmentPayments' => function ($q) use ($currentSy) {
                $q->where('semester', $currentSy)->orderByDesc('created_at');
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
            $students->whereDoesntHave('enrollmentPayments', function ($q) use ($currentSy) {
                $q->where('semester', $currentSy)->where('status', 'paid');
            });
        } elseif ($status === 'pending') {
            $students->whereHas('enrollmentPayments', function ($q) use ($currentSy) {
                $q->where('semester', $currentSy)->where('status', 'pending');
            });
        } elseif ($status === 'paid') {
            $students->whereHas('enrollmentPayments', function ($q) use ($currentSy) {
                $q->where('semester', $currentSy)->where('status', 'paid');
            });
        }

        $students = $students->orderBy('fullname')->get();

        $departments = User::where('role', 'student')->select('department')->distinct()->pluck('department');
        $years = User::where('role', 'student')->select('year_level')->distinct()->pluck('year_level');

        return view('admin.enrollment_payments', compact('students', 'search', 'dept', 'year', 'status', 'departments', 'years', 'currentSy'));
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
        $currentSy = config('ssc.current_school_year');
        $amount = config('ssc.enrollment_fee_amount', 50);

        $payment = EnrollmentPayment::where('user_id', $student->id)
            ->where('semester', $currentSy)
            ->latest()
            ->first();

        if (! $payment) {
            $payment = EnrollmentPayment::create([
                'user_id' => $student->id,
                'amount' => $amount,
                'semester' => $currentSy,
                'method' => 'walk_in',
                'status' => 'paid',
                'proof_status' => 'approved',
                'reference' => 'WALKIN-' . strtoupper(uniqid()),
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
                'reference' => $payment->reference ?: 'WALKIN-' . strtoupper(uniqid()),
            ]);
        }

        $this->addEnrollmentBudget($payment);

        SscHelper::logActivity(Auth::id(), 'ENROLLMENT_MARK_PAID', "Marked walk-in enrollment payment for student {$student->email}");

        try {
            $student->notify(new EnrollmentPaidNotification($payment));
        } catch (\Throwable $e) {
            // optional
        }

        return redirect()->back()->with('success', 'Walk-in payment recorded and student notified.');
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

    protected function addEnrollmentBudget(EnrollmentPayment $payment)
    {
        $budget = Budget::firstOrCreate(
            ['title' => 'Enrollment Fees', 'school_year' => config('ssc.current_school_year') ?? 'default'],
            ['department' => 'General', 'allocated_amount' => 0, 'remaining_balance' => 0, 'status' => 'Pending', 'created_by' => Auth::id()]
        );

        $budget->allocated_amount += $payment->amount;
        $budget->remaining_balance += $payment->amount;
        $budget->save();

        return $budget;
    }
}
