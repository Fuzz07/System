<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EnrollmentPayment;

class EnrollmentController extends Controller
{
    public function index()
    {
        $student = Auth::user();
        $currentSy = config('ssc.current_school_year');

        $payment = EnrollmentPayment::where('user_id', $student->id)
            ->where('semester', $currentSy)
            ->orderByDesc('created_at')
            ->first();

        $amount = config('ssc.enrollment_fee_amount', 50);

        return view('student.enrollment', compact('payment', 'amount'));
    }

    public function store(Request $request)
    {
        $student = Auth::user();
        $amount = config('ssc.enrollment_fee_amount', 50);
        $currentSy = config('ssc.current_school_year');

        // Create a pending payment record; real GCash integration can update it via webhook
        $payment = EnrollmentPayment::create([
            'user_id' => $student->id,
            'amount' => $amount,
            'semester' => $currentSy,
            'method' => 'gcash',
            'status' => 'pending',
            'reference' => 'GCASH-' . strtoupper(uniqid()),
        ]);

        return redirect()->route('student.enrollment.index')->with('success', 'Payment record created. Please follow the GCash instructions to complete payment. Use reference: ' . $payment->reference);
    }
}
