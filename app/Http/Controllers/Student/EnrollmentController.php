<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\EnrollmentPayment;
use App\Helpers\SscHelper;

class EnrollmentController extends Controller
{
    public function index()
    {
        $student = Auth::user();
        $currentSy = SscHelper::getActiveSchoolYear();

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
        $currentSy = SscHelper::getActiveSchoolYear();

        $payment = EnrollmentPayment::where('user_id', $student->id)
            ->where('semester', $currentSy)
            ->orderByDesc('created_at')
            ->first();

        if ($payment && $payment->status === 'paid') {
            return redirect()->route('student.enrollment.index')->with('info', 'Your enrollment fee is already marked as paid.');
        }

        $request->validate([
            'payment_method' => 'nullable|in:gcash,instapay',
            'proof' => 'required|file|extensions:jpg,jpeg,png,pdf,mp4|max:5120',
        ], [
            'proof.required' => 'Please attach a proof of payment before submitting.',
            'proof.file' => 'The proof of payment must be a valid file.',
            'proof.extensions' => 'The proof of payment must be an image (jpg, jpeg, png), PDF, or MP4 video.',
            'proof.max' => 'The proof of payment must not exceed 5MB.',
        ]);

        $method = $request->input('payment_method', $payment->method ?? 'gcash');
        $prefix = $method === 'instapay' ? 'INSTAPAY-' : 'GCASH-';

        if (! $payment) {
            $payment = EnrollmentPayment::create([
                'user_id' => $student->id,
                'amount' => $amount,
                'semester' => $currentSy,
                'method' => $method,
                'status' => 'pending',
                'reference' => $prefix . strtoupper(uniqid()),
                'proof_status' => 'pending',
            ]);
        } else {
            if ($request->filled('payment_method')) {
                $payment->update(['method' => $method]);
            }
        }

        try {
            $proofPath = \App\Helpers\SscHelper::uploadToCloudinary($request->file('proof'), 'enrollment_proofs');
        } catch (\Exception $e) {
            \Log::warning('Cloudinary upload failed for student enrollment proof, falling back to local public disk: ' . $e->getMessage());
            $proofPath = $request->file('proof')->store('enrollment_proofs', 'public');
        }

        $payment->update([
            'proof_path' => $proofPath,
            'proof_status' => 'pending',
            'proof_notes' => null,
        ]);

        return redirect()->route('student.enrollment.index')->with('success', 'Payment proof uploaded successfully. Admin will verify it shortly.');
    }
}
