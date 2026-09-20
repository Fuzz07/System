<?php

namespace App\Http\Controllers;

use App\Models\EnrollmentPayment;
use App\Services\EnrollmentPaymentSettlementService;
use App\Services\PayMongoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayMongoWebhookController extends Controller
{
    public function __invoke(
        Request $request,
        PayMongoService $payMongo,
        EnrollmentPaymentSettlementService $settlement
    ): JsonResponse {
        $rawPayload = $request->getContent();

        if (! $payMongo->verifyWebhookSignature($rawPayload, $request->header('Paymongo-Signature'))) {
            Log::warning('Rejected a PayMongo webhook with an invalid signature.');

            return response()->json(['message' => 'Invalid signature.'], 401);
        }

        $payload = json_decode($rawPayload, true);
        if (! is_array($payload)) {
            return response()->json(['message' => 'Invalid payload.'], 400);
        }

        $event = $payload['data'] ?? [];
        $eventType = $event['type'] ?? data_get($event, 'attributes.type');

        if ($eventType !== 'checkout_session.payment.paid') {
            return response()->json(['received' => true]);
        }

        $session = $event['data'] ?? data_get($event, 'attributes.data');
        if (! is_array($session)) {
            Log::warning('PayMongo paid webhook did not contain a checkout session.');

            return response()->json(['received' => true]);
        }

        $sessionId = $session['id'] ?? null;
        $reference = data_get($session, 'attributes.reference_number');

        $payment = EnrollmentPayment::query()
            ->where('paymongo_checkout_session_id', $sessionId)
            ->first();

        if (! $payment && filled($reference)) {
            $payment = EnrollmentPayment::query()
                ->where('method', 'paymongo')
                ->where('reference', $reference)
                ->first();
        }

        if (! $payment
            || $payment->paymongo_checkout_session_id !== $sessionId
            || $payment->reference !== $reference) {
            Log::notice('Ignored an unmatched PayMongo checkout webhook.', [
                'checkout_session_id' => $sessionId,
                'reference' => $reference,
            ]);

            return response()->json(['received' => true]);
        }

        $paidPayment = $payMongo->paidPayment(
            $session,
            (int) round((float) $payment->amount * 100)
        );

        if (! $paidPayment) {
            Log::warning('PayMongo paid webhook failed amount, currency, or payment-status validation.', [
                'payment_id' => $payment->id,
                'checkout_session_id' => $sessionId,
            ]);

            return response()->json(['received' => true]);
        }

        $settlement->settlePayMongoPayment(
            $payment,
            $paidPayment,
            data_get($paidPayment, 'attributes.paid_at')
        );

        return response()->json(['received' => true]);
    }
}
