<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PayMongoService
{
    public function isConfigured(): bool
    {
        return (bool) config('services.paymongo.enabled')
            && filled(config('services.paymongo.secret_key'));
    }

    public function createCheckoutSession(array $attributes, string $idempotencyKey): array
    {
        $response = $this->client()
            ->withHeader('Idempotency-Key', $idempotencyKey)
            ->post('/v2/checkout_sessions', ['data' => ['attributes' => $attributes]]);

        return $this->checkoutSessionFrom($response);
    }

    public function retrieveCheckoutSession(string $sessionId): array
    {
        $response = $this->client()->get('/v1/checkout_sessions/' . rawurlencode($sessionId));

        return $this->checkoutSessionFrom($response);
    }

    public function expireCheckoutSession(string $sessionId): array
    {
        $response = $this->client()->post('/v1/checkout_sessions/' . rawurlencode($sessionId) . '/expire');

        return $this->checkoutSessionFrom($response);
    }

    public function paidPayment(array $session, int $expectedAmount): ?array
    {
        $payments = data_get($session, 'attributes.payments', []);

        foreach (is_array($payments) ? $payments : [] as $payment) {
            $attributes = $payment['attributes'] ?? [];

            if (($attributes['status'] ?? null) === 'paid'
                && ($attributes['currency'] ?? null) === 'PHP'
                && (int) ($attributes['amount'] ?? 0) === $expectedAmount) {
                return $payment;
            }
        }

        return null;
    }

    public function verifyWebhookSignature(string $payload, ?string $header): bool
    {
        $secret = (string) config('services.paymongo.webhook_secret');

        if ($secret === '' || blank($header)) {
            return false;
        }

        $parts = [];
        foreach (explode(',', $header) as $part) {
            [$key, $value] = array_pad(explode('=', trim($part), 2), 2, null);
            if ($key !== null && $value !== null) {
                $parts[$key] = $value;
            }
        }

        $timestamp = $parts['t'] ?? null;
        if (! is_string($timestamp) || ! ctype_digit($timestamp)) {
            return false;
        }

        $tolerance = max(0, (int) config('services.paymongo.webhook_tolerance', 300));
        if ($tolerance > 0 && abs(time() - (int) $timestamp) > $tolerance) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);

        foreach (['te', 'li'] as $mode) {
            if (! empty($parts[$mode]) && hash_equals($expected, $parts[$mode])) {
                return true;
            }
        }

        return false;
    }

    private function client()
    {
        $secretKey = (string) config('services.paymongo.secret_key');

        if (! $this->isConfigured()) {
            throw new RuntimeException('PayMongo is not configured.');
        }

        return Http::baseUrl(rtrim((string) config('services.paymongo.api_url'), '/'))
            ->withBasicAuth($secretKey, '')
            ->acceptJson()
            ->asJson()
            ->connectTimeout(5)
            ->timeout(15);
    }

    private function checkoutSessionFrom(Response $response): array
    {
        if ($response->failed()) {
            $detail = data_get($response->json(), 'errors.0.detail', 'The payment provider rejected the request.');
            throw new RuntimeException("PayMongo request failed ({$response->status()}): {$detail}");
        }

        $session = $response->json('data');
        if (! is_array($session) || blank($session['id'] ?? null)) {
            throw new RuntimeException('PayMongo returned an invalid checkout session.');
        }

        return $session;
    }
}
