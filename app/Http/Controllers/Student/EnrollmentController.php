<?php

namespace App\Http\Controllers\Student;

use App\Helpers\SscHelper;
use App\Http\Controllers\Controller;
use App\Models\EnrollmentPayment;
use App\Services\EnrollmentPaymentSettlementService;
use App\Services\PayMongoService;
use App\Support\UploadValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Throwable;

class EnrollmentController extends Controller
{
    public function __construct(
        private readonly PayMongoService $payMongo,
        private readonly EnrollmentPaymentSettlementService $settlement
    ) {}

    public function index()
    {
        return view('student.enrollment', $this->paymentContext());
    }

    public function mobileIndex()
    {
        return view('mobile.student.enrollment', $this->paymentContext());
    }

    public function store(Request $request)
    {
        $student = Auth::user();
        $amount = config('ssc.enrollment_fee_amount', 50);
        $currentSy = SscHelper::getActiveAcademicTerm();
        $payment = $this->currentPayment();
        $redirectRoute = $this->indexRoute($request);

        if ($payment && $payment->status === 'paid') {
            return redirect()->route($redirectRoute)->with('info', 'Your contribution fee is already marked as paid.');
        }

        $request->validate([
            'payment_method' => 'nullable|in:gcash,instapay',
            'proof' => UploadValidation::requiredFile(),
        ], [
            'proof.required' => 'Please attach a proof of payment before submitting.',
            'proof.file' => 'The proof of payment must be a valid file.',
            'proof.mimes' => 'The proof of payment must be a PNG, JPG or JPEG image.',
            'proof.extensions' => 'The proof of payment must be a PNG, JPG or JPEG image.',
            'proof.max' => 'The proof of payment must not exceed 5MB.',
        ]);

        $method = $request->input(
            'payment_method',
            in_array($payment?->method, ['gcash', 'instapay'], true) ? $payment->method : 'gcash'
        );
        $prefix = $method === 'instapay' ? 'INSTAPAY-' : 'GCASH-';

        if (! $payment) {
            $payment = EnrollmentPayment::create([
                'user_id' => $student->id,
                'amount' => $amount,
                'semester' => $currentSy,
                'method' => $method,
                'status' => 'pending',
                'reference' => $prefix . Str::upper(Str::random(12)),
                'proof_status' => 'pending',
            ]);
        } else {
            $payment->update(['method' => $method]);
        }

        try {
            $proofPath = SscHelper::uploadToCloudinary($request->file('proof'), 'enrollment_proofs');
        } catch (Throwable $exception) {
            Log::warning('Cloudinary enrollment proof upload failed; using the public disk.', [
                'payment_id' => $payment->id,
                'message' => $exception->getMessage(),
            ]);
            $proofPath = $request->file('proof')->store('enrollment_proofs', 'public');
        }

        $payment->update([
            'proof_path' => $proofPath,
            'proof_status' => 'pending',
            'proof_notes' => null,
        ]);

        return redirect()->route($redirectRoute)
            ->with('success', 'Payment proof uploaded successfully. The SSC office will verify it shortly.');
    }

    public function startPayMongo(Request $request)
    {
        $redirectRoute = $this->indexRoute($request);

        if (! $this->payMongo->isConfigured()) {
            $message = 'Online payment is temporarily unavailable. Please use a manual payment option or contact the SSC office.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 503);
            }

            return redirect()->route($redirectRoute)
                ->with('error', $message);
        }

        $student = Auth::user();
        $payment = $this->currentPayment();
        $amount = (float) config('ssc.enrollment_fee_amount', 50);
        $amountInCentavos = (int) round($amount * 100);

        if ($payment?->status === 'paid') {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your contribution fee is already paid.',
                    'paid' => true,
                ]);
            }

            return redirect()->route($redirectRoute)->with('info', 'Your contribution fee is already paid.');
        }

        if ($payment?->paymongo_checkout_session_id) {
            try {
                $session = $this->payMongo->retrieveCheckoutSession($payment->paymongo_checkout_session_id);

                if ($this->sessionBelongsToPayment($session, $payment)) {
                    if ($paidPayment = $this->payMongo->paidPayment($session, $amountInCentavos)) {
                        $this->settlement->settlePayMongoPayment(
                            $payment,
                            $paidPayment,
                            data_get($paidPayment, 'attributes.paid_at')
                        );

                        return redirect()->route($redirectRoute)
                            ->with('success', 'Payment confirmed. Your contribution fee is now marked as paid.');
                    }

                    $checkoutUrl = data_get($session, 'attributes.checkout_url');
                    if (data_get($session, 'attributes.status') === 'active' && $this->isPayMongoCheckoutUrl($checkoutUrl)) {
                        if ($request->expectsJson()) {
                            return response()->json(['checkout_url' => $checkoutUrl]);
                        }

                        return redirect()->away($checkoutUrl);
                    }
                }
            } catch (Throwable $exception) {
                Log::notice('Existing PayMongo checkout could not be resumed.', [
                    'payment_id' => $payment->id,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        if (! $payment) {
            $payment = EnrollmentPayment::create([
                'user_id' => $student->id,
                'amount' => $amount,
                'semester' => SscHelper::getActiveAcademicTerm(),
                'method' => 'paymongo',
                'status' => 'pending',
                'reference' => 'PM-' . Str::upper(Str::random(16)),
                'proof_status' => 'pending',
            ]);
        }

        $returnRoute = $request->routeIs('mobile.*')
            ? 'mobile.student.enrollment.paymongo.return'
            : 'student.enrollment.paymongo.return';

        try {
            $session = $this->payMongo->createCheckoutSession([
                'billing' => [
                    'name' => $student->fullname,
                    'email' => $student->email,
                ],
                'line_items' => [[
                    'name' => 'Semester Contribution Fee',
                    'description' => SscHelper::getActiveAcademicTerm(),
                    'amount' => $amountInCentavos,
                    'currency' => 'PHP',
                    'quantity' => 1,
                ]],
                'payment_method_types' => config('services.paymongo.payment_methods'),
                'success_url' => URL::temporarySignedRoute(
                    $returnRoute,
                    now()->addDays(2),
                    ['payment' => $payment->id]
                ),
                'cancel_url' => route($redirectRoute, ['paymongo' => 'cancelled']),
                'description' => 'SSC semester contribution fee for ' . SscHelper::getActiveAcademicTerm(),
                'reference_number' => $payment->reference,
                'send_email_receipt' => true,
                'show_description' => true,
                'show_line_items' => true,
                'metadata' => [
                    'enrollment_payment_id' => (string) $payment->id,
                    'student_id' => (string) $student->id,
                    'academic_term' => (string) $payment->semester,
                ],
            ], $payment->reference);

            $checkoutUrl = data_get($session, 'attributes.checkout_url');
            if (! $this->isPayMongoCheckoutUrl($checkoutUrl)) {
                throw new \RuntimeException('PayMongo returned an invalid checkout URL.');
            }

            $payment->update([
                'amount' => $amount,
                'method' => 'paymongo',
                'paymongo_checkout_session_id' => $session['id'],
            ]);

            if ($request->expectsJson()) {
                return response()->json(['checkout_url' => $checkoutUrl]);
            }

            return redirect()->away($checkoutUrl);
        } catch (Throwable $exception) {
            Log::error('Unable to start PayMongo enrollment checkout.', [
                'payment_id' => $payment->id,
                'message' => $exception->getMessage(),
            ]);

            $message = 'We could not open secure checkout right now. No charge was made. Please try again.';
            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 502);
            }

            return redirect()->route($redirectRoute)
                ->with('error', $message);
        }
    }

    public function returnFromPayMongo(Request $request, EnrollmentPayment $payment)
    {
        abort_unless($payment->user_id === Auth::id(), 403);
        $redirectRoute = $this->indexRoute($request);

        if ($payment->status === 'paid') {
            return redirect()->route($redirectRoute)
                ->with('success', 'Payment confirmed. Your contribution fee is paid.');
        }

        if (blank($payment->paymongo_checkout_session_id)) {
            return redirect()->route($redirectRoute)
                ->with('error', 'We could not match this checkout to a contribution fee payment.');
        }

        try {
            $session = $this->payMongo->retrieveCheckoutSession($payment->paymongo_checkout_session_id);
            $paidPayment = $this->sessionBelongsToPayment($session, $payment)
                ? $this->payMongo->paidPayment($session, (int) round((float) $payment->amount * 100))
                : null;

            if (! $paidPayment) {
                return redirect()->route($redirectRoute)
                    ->with('info', 'Your payment is still processing. We will update the status automatically once PayMongo confirms it.');
            }

            $this->settlement->settlePayMongoPayment(
                $payment,
                $paidPayment,
                data_get($paidPayment, 'attributes.paid_at')
            );

            return redirect()->route($redirectRoute)
                ->with('success', 'Payment confirmed. Your contribution fee is now marked as paid.');
        } catch (Throwable $exception) {
            Log::error('Unable to verify the returned PayMongo enrollment checkout.', [
                'payment_id' => $payment->id,
                'message' => $exception->getMessage(),
            ]);

            return redirect()->route($redirectRoute)
                ->with('info', 'Your payment is being verified. Please refresh this page in a moment.');
        }
    }

    private function paymentContext(): array
    {
        return [
            'payment' => $this->currentPayment(),
            'amount' => config('ssc.enrollment_fee_amount', 50),
            'currentSy' => SscHelper::getActiveAcademicTerm(),
            'paymongoEnabled' => $this->payMongo->isConfigured(),
        ];
    }

    private function currentPayment(): ?EnrollmentPayment
    {
        return EnrollmentPayment::query()
            ->where('user_id', Auth::id())
            ->whereIn('semester', SscHelper::getActiveEnrollmentTermKeys())
            ->latest()
            ->first();
    }

    private function indexRoute(Request $request): string
    {
        return $request->routeIs('mobile.*')
            ? 'mobile.student.enrollment'
            : 'student.enrollment.index';
    }

    private function sessionBelongsToPayment(array $session, EnrollmentPayment $payment): bool
    {
        return ($session['id'] ?? null) === $payment->paymongo_checkout_session_id
            && data_get($session, 'attributes.reference_number') === $payment->reference;
    }

    private function isPayMongoCheckoutUrl(mixed $url): bool
    {
        if (! is_string($url) || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        return parse_url($url, PHP_URL_SCHEME) === 'https'
            && ($host === 'checkout.paymongo.com' || str_ends_with($host, '.checkout.paymongo.com'));
    }
}
