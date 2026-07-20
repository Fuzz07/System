<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EnrollmentPayment;
use App\Models\Budget;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\SscHelper;

class EnrollmentPaymentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $dept = $request->input('department');
        $year = $request->input('year_level');

        $query = EnrollmentPayment::with('user');

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('fullname', 'like', "%$search%")->orWhere('student_id', 'like', "%$search%");
            });
        }

        if ($dept) {
            $query->whereHas('user', fn($q)=> $q->where('department', $dept));
        }

        if ($year) {
            $query->whereHas('user', fn($q)=> $q->where('year_level', $year));
        }

        $payments = $query->orderByDesc('created_at')->get();

        $departments = User::where('role', 'student')->select('department')->distinct()->pluck('department');
        $years = User::where('role', 'student')->select('year_level')->distinct()->pluck('year_level');

        return view('admin.enrollment_payments', compact('payments', 'search', 'dept', 'year', 'departments', 'years'));
    }

    public function markPaid(EnrollmentPayment $payment, Request $request)
    {
        if ($payment->status === 'paid') {
            return redirect()->back()->with('info', 'Already marked paid.');
        }

        $payment->update([
            'status' => 'paid',
            'admin_marked_by' => Auth::id(),
            'paid_at' => now(),
        ]);

        // Add to SSC budget as income (create or increment a budget titled 'Enrollment Fees')
        $budget = Budget::firstOrCreate(
            ['title' => 'Enrollment Fees', 'school_year' => config('ssc.current_school_year') ?? 'default'],
            ['department' => 'General', 'allocated_amount' => 0, 'remaining_balance' => 0, 'status' => 'active', 'created_by' => Auth::id()]
        );

        $budget->allocated_amount += $payment->amount;
        $budget->remaining_balance += $payment->amount;
        $budget->save();

        SscHelper::logActivity(Auth::id(), 'ENROLLMENT_MARK_PAID', "Marked enrollment payment #{$payment->id} as paid for user {$payment->user->email}");

        return redirect()->back()->with('success', 'Payment marked as paid and added to budget.');
    }
}
