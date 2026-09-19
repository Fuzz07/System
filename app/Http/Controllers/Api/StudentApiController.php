<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EnrollmentPayment;
use App\Helpers\SscHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class StudentApiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('role:student');
    }

    /**
     * Get detailed student profile.
     */
    public function profile()
    {
        /** @var \App\Models\User $student */
        $student = auth('api')->user();

        return response()->json([
            'success' => true,
            'data'    => [
                'id'            => $student->id,
                'student_id'    => $student->student_id,
                'fullname'      => $student->fullname,
                'first_name'    => $student->first_name,
                'middle_name'   => $student->middle_name,
                'last_name'     => $student->last_name,
                'email'         => $student->email,
                'department'    => $student->department,
                'year_level'    => $student->year_level,
                'age'           => $student->age,
                'status'        => $student->status,
                'photo_url'     => $student->photo_url,
                'avatar'        => $student->avatar,
                'is_graduated'  => $student->isGraduated(),
            ],
        ]);
    }

    /**
     * Get current student enrollment payment status.
     */
    public function enrollment()
    {
        $student = auth('api')->user();
        $currentSy = SscHelper::getActiveAcademicTerm();
        $currentTermKeys = SscHelper::getActiveEnrollmentTermKeys();

        $payment = EnrollmentPayment::where('user_id', $student->id)
            ->whereIn('semester', $currentTermKeys)
            ->orderByDesc('created_at')
            ->first();

        $amount = config('ssc.enrollment_fee_amount', 50);

        return response()->json([
            'success' => true,
            'data'    => [
                'semester'       => $currentSy,
                'required_fee'   => (float) $amount,
                'is_paid'        => $payment?->status === 'paid',
                'payment'        => $payment ? [
                    'id'           => $payment->id,
                    'reference'    => $payment->reference,
                    'amount'       => (float) $payment->amount,
                    'method'       => $payment->method,
                    'status'       => $payment->status,
                    'proof_status' => $payment->proof_status,
                    'proof_url'    => $payment->proof_path ? SscHelper::getUploadUrl($payment->proof_path) : null,
                    'proof_notes'  => $payment->proof_notes,
                    'created_at'   => $payment->created_at?->toIso8601String(),
                ] : null,
            ],
        ]);
    }

    /**
     * Submit proof of payment for enrollment fee.
     */
    public function uploadEnrollmentProof(Request $request)
    {
        $student = auth('api')->user();
        $amount = config('ssc.enrollment_fee_amount', 50);
        $currentSy = SscHelper::getActiveAcademicTerm();
        $currentTermKeys = SscHelper::getActiveEnrollmentTermKeys();

        $payment = EnrollmentPayment::where('user_id', $student->id)
            ->whereIn('semester', $currentTermKeys)
            ->orderByDesc('created_at')
            ->first();

        if ($payment && $payment->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Your enrollment fee is already marked as paid.',
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'payment_method' => 'nullable|in:gcash,instapay',
            'proof'          => 'required|file|mimes:jpg,jpeg,png,pdf,mp4|max:5120',
        ], [
            'proof.required' => 'Please attach a proof of payment before submitting.',
            'proof.file'     => 'The proof of payment must be a valid file.',
            'proof.mimes'    => 'The proof of payment must be an image (jpg, jpeg, png), PDF, or MP4 video.',
            'proof.max'      => 'The proof of payment must not exceed 5MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $method = $request->input('payment_method', $payment->method ?? 'gcash');
        $prefix = $method === 'instapay' ? 'INSTAPAY-' : 'GCASH-';

        if (! $payment) {
            $payment = EnrollmentPayment::create([
                'user_id'      => $student->id,
                'amount'       => $amount,
                'semester'     => $currentSy,
                'method'       => $method,
                'status'       => 'pending',
                'reference'    => $prefix . strtoupper(uniqid()),
                'proof_status' => 'pending',
            ]);
        } else {
            if ($request->filled('payment_method')) {
                $payment->update(['method' => $method]);
            }
        }

        try {
            $proofPath = SscHelper::uploadToCloudinary($request->file('proof'), 'enrollment_proofs');
        } catch (\Exception $e) {
            Log::warning('Cloudinary upload failed for API student enrollment proof, falling back to local storage: ' . $e->getMessage());
            $proofPath = $request->file('proof')->store('enrollment_proofs', 'public');
        }

        $payment->update([
            'proof_path'   => $proofPath,
            'proof_status' => 'pending',
            'proof_notes'  => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment proof uploaded successfully. An administrator will verify it shortly.',
            'data'    => [
                'reference'    => $payment->reference,
                'method'       => $payment->method,
                'proof_status' => $payment->proof_status,
                'proof_url'    => SscHelper::getUploadUrl($proofPath),
            ],
        ]);
    }
}
