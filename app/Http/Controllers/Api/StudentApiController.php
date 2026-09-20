<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EnrollmentPayment;
use App\Helpers\SscHelper;
use App\Services\EnrollmentPaymentSettlementService;
use App\Services\PayMongoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Throwable;

class StudentApiController extends Controller
{
    public function __construct(
        private readonly PayMongoService $payMongo,
        private readonly EnrollmentPaymentSettlementService $settlement
    ) {
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

    /**
     * Initiate a PayMongo checkout session for the student's enrollment fee.
     *
     * Returns a checkout_url that the mobile app should open in a WebView
     * or in-app browser. After payment, the user is redirected to the
     * success_url which the app should intercept to call the verify endpoint.
     */
    public function startPayMongoCheckout(Request $request): JsonResponse
    {
        if (! $this->payMongo->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'Online payment is temporarily unavailable. Please use a manual payment option.',
            ], 503);
        }

        /** @var \App\Models\User $student */
        $student         = auth('api')->user();
        $amount          = (float) config('ssc.enrollment_fee_amount', 50);
        $amountCentavos  = (int) round($amount * 100);
        $currentTermKeys = SscHelper::getActiveEnrollmentTermKeys();

        $payment = EnrollmentPayment::where('user_id', $student->id)
            ->whereIn('semester', $currentTermKeys)
            ->orderByDesc('created_at')
            ->first();

        if ($payment?->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Your enrollment fee is already paid.',
                'is_paid' => true,
            ], 409);
        }

        // Attempt to reuse an existing active checkout session.
        if ($payment?->paymongo_checkout_session_id) {
            try {
                $session      = $this->payMongo->retrieveCheckoutSession($payment->paymongo_checkout_session_id);
                $checkoutUrl  = data_get($session, 'attributes.checkout_url');

                if (data_get($session, 'attributes.status') === 'active'
                    && is_string($checkoutUrl)
                    && str_starts_with($checkoutUrl, 'https://checkout.paymongo.com')) {
                    return response()->json([
                        'success'      => true,
                        'checkout_url' => $checkoutUrl,
                        'reference'    => $payment->reference,
                        'payment_id'   => $payment->id,
                    ]);
                }

                // Session is no longer active — fall through to create a new one.
            } catch (Throwable $exception) {
                Log::notice('Could not resume existing PayMongo session from API.', [
                    'payment_id' => $payment?->id,
                    'message'    => $exception->getMessage(),
                ]);
            }
        }

        if (! $payment) {
            $payment = EnrollmentPayment::create([
                'user_id'      => $student->id,
                'amount'       => $amount,
                'semester'     => SscHelper::getActiveAcademicTerm(),
                'method'       => 'paymongo',
                'status'       => 'pending',
                'reference'    => 'PM-' . Str::upper(Str::random(16)),
                'proof_status' => 'pending',
            ]);
        }

        try {
            // The success URL uses a signed route; the mobile app should
            // intercept navigation to this host/path and call verifyPayMongoCheckout.
            $successUrl = route('api.student.enrollment.paymongo.verify', [
                'payment' => $payment->id,
            ]);

            $session = $this->payMongo->createCheckoutSession([
                'billing' => [
                    'name'  => $student->fullname,
                    'email' => $student->email,
                ],
                'line_items' => [[
                    'name'        => 'Semester Enrollment Fee',
                    'description' => SscHelper::getActiveAcademicTerm(),
                    'amount'      => $amountCentavos,
                    'currency'    => 'PHP',
                    'quantity'    => 1,
                ]],
                'payment_method_types' => config('services.paymongo.payment_methods'),
                'success_url'          => $successUrl,
                'cancel_url'           => route('api.student.enrollment.paymongo.cancel', [
                    'payment' => $payment->id,
                ]),
                'description'          => 'SSC semester enrollment fee for ' . SscHelper::getActiveAcademicTerm(),
                'reference_number'     => $payment->reference,
                'send_email_receipt'   => true,
                'show_description'     => true,
                'show_line_items'      => true,
                'metadata'             => [
                    'enrollment_payment_id' => (string) $payment->id,
                    'student_id'            => (string) $student->id,
                    'academic_term'         => (string) $payment->semester,
                ],
            ], 'PM-API-' . $payment->id . '-' . time());

            $checkoutUrl = data_get($session, 'attributes.checkout_url');
            if (! is_string($checkoutUrl) || ! str_starts_with($checkoutUrl, 'https://checkout.paymongo.com')) {
                throw new \RuntimeException('PayMongo returned an invalid checkout URL.');
            }

            $payment->update([
                'amount'                       => $amount,
                'method'                       => 'paymongo',
                'paymongo_checkout_session_id' => $session['id'],
            ]);

            return response()->json([
                'success'      => true,
                'checkout_url' => $checkoutUrl,
                'reference'    => $payment->reference,
                'payment_id'   => $payment->id,
            ]);
        } catch (Throwable $exception) {
            Log::error('Unable to start PayMongo enrollment checkout from API.', [
                'payment_id' => $payment->id,
                'message'    => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'We could not open secure checkout right now. No charge was made. Please try again.',
            ], 500);
        }
    }

    /**
     * Verify a PayMongo payment after the student returns from checkout.
     * The mobile app should call this after intercepting the success_url redirect.
     */
    public function verifyPayMongoCheckout(Request $request, EnrollmentPayment $payment): JsonResponse
    {
        /** @var \App\Models\User $student */
        $student = auth('api')->user();

        if ($payment->user_id !== $student->id) {
            return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
        }

        if ($payment->status === 'paid') {
            return response()->json([
                'success' => true,
                'is_paid' => true,
                'message' => 'Payment confirmed. Your enrollment fee is paid.',
            ]);
        }

        if (blank($payment->paymongo_checkout_session_id)) {
            return response()->json([
                'success' => false,
                'message' => 'No checkout session found for this payment.',
            ], 400);
        }

        try {
            $session    = $this->payMongo->retrieveCheckoutSession($payment->paymongo_checkout_session_id);
            $paidPayment = ($session['id'] ?? null) === $payment->paymongo_checkout_session_id
                && data_get($session, 'attributes.reference_number') === $payment->reference
                ? $this->payMongo->paidPayment($session, (int) round((float) $payment->amount * 100))
                : null;

            if (! $paidPayment) {
                return response()->json([
                    'success' => true,
                    'is_paid' => false,
                    'message' => 'Your payment is still processing. We will update the status automatically once PayMongo confirms it.',
                ]);
            }

            $this->settlement->settlePayMongoPayment(
                $payment,
                $paidPayment,
                data_get($paidPayment, 'attributes.paid_at')
            );

            return response()->json([
                'success' => true,
                'is_paid' => true,
                'message' => 'Payment confirmed. Your enrollment fee is now marked as paid.',
            ]);
        } catch (Throwable $exception) {
            Log::error('Unable to verify PayMongo checkout from API.', [
                'payment_id' => $payment->id,
                'message'    => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed. Please refresh and try again.',
            ], 500);
        }
    }

    /**
     * Handle a cancelled PayMongo checkout session from the mobile app.
     */
    public function cancelPayMongoCheckout(Request $request, EnrollmentPayment $payment): JsonResponse
    {
        /** @var \App\Models\User $student */
        $student = auth('api')->user();

        if ($payment->user_id !== $student->id) {
            return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Checkout cancelled. You were not charged.',
        ]);
    }
}
