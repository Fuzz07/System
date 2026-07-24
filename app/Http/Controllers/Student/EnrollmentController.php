<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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

        $request->validate([
            'payment_method' => 'nullable|in:gcash,instapay',
        ]);

        $method = $request->input('payment_method', 'gcash');
        $prefix = $method === 'instapay' ? 'INSTAPAY-' : 'GCASH-';

        $payment = EnrollmentPayment::where('user_id', $student->id)
            ->where('semester', $currentSy)
            ->orderByDesc('created_at')
            ->first();

        if ($request->hasFile('proof')) {
            $request->validate([
                'proof' => 'required|file|mimes:jpg,jpeg,png,pdf,mp4|max:5120',
            ]);

            if (! $payment || $payment->status === 'paid') {
                $payment = EnrollmentPayment::create([
                    'user_id' => $student->id,
                    'amount' => $amount,
                    'semester' => $currentSy,
                    'method' => $method,
                    'status' => 'pending',
                    'reference' => $prefix . strtoupper(uniqid()),
                    'proof_status' => 'pending',
                ]);
            }

            $proofPath = $request->file('proof')->store('enrollment_proofs', 'public');
            $payment->update([
                'proof_path' => $proofPath,
                'proof_status' => 'pending',
                'proof_notes' => null,
            ]);

            return redirect()->route('student.enrollment.index')->with('success', 'Payment proof uploaded successfully. Admin will verify it shortly.');
        }

        if ($payment && $payment->status === 'pending') {
            return redirect()->route('student.enrollment.index')->with('info', 'You already have a pending payment. Please upload proof or wait for admin verification.');
        }

        if ($payment && $payment->status === 'paid') {
            return redirect()->route('student.enrollment.index')->with('info', 'Your enrollment fee is already marked as paid.');
        }

        $payment = EnrollmentPayment::create([
            'user_id' => $student->id,
            'amount' => $amount,
            'semester' => $currentSy,
            'method' => $method,
            'status' => 'pending',
            'reference' => $prefix . strtoupper(uniqid()),
            'proof_status' => 'pending',
        ]);

        $methodLabel = $method === 'instapay' ? 'InstaPay' : 'GCash';
        return redirect()->route('student.enrollment.index')->with('success', 'Payment record created. Please follow the ' . $methodLabel . ' instructions to complete payment and upload proof once sent. Reference: ' . $payment->reference);
    }
}
