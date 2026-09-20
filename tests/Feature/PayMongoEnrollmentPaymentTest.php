<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\EnrollmentPayment;
use App\Models\SchoolYear;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PayMongoEnrollmentPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.paymongo.enabled' => true,
            'services.paymongo.secret_key' => 'sk_test_example',
            'services.paymongo.webhook_secret' => 'whsk_test_example',
            'services.paymongo.api_url' => 'https://api.paymongo.com',
            'services.paymongo.payment_methods' => ['gcash', 'qrph', 'card'],
            'services.paymongo.webhook_tolerance' => 300,
            'ssc.enrollment_fee_amount' => 50,
        ]);

        SchoolYear::create([
            'label' => '2026-2027',
            'semester' => SchoolYear::SEMESTER_FIRST,
            'is_active' => true,
        ]);
    }

    public function test_student_can_start_a_secure_paymongo_checkout(): void
    {
        Http::fake([
            'https://api.paymongo.com/v2/checkout_sessions' => Http::response([
                'data' => [
                    'id' => 'cs_test_checkout',
                    'type' => 'checkout_session',
                    'attributes' => [
                        'checkout_url' => 'https://checkout.paymongo.com/cs_test_checkout',
                        'status' => 'active',
                    ],
                ],
            ]),
        ]);

        $student = $this->student('checkout.student@example.com');

        $this->actingAs($student)
            ->post(route('student.enrollment.paymongo.checkout'))
            ->assertRedirect('https://checkout.paymongo.com/cs_test_checkout');

        $payment = EnrollmentPayment::where('user_id', $student->id)->firstOrFail();
        $this->assertSame('paymongo', $payment->method);
        $this->assertSame('pending', $payment->status);
        $this->assertSame('cs_test_checkout', $payment->paymongo_checkout_session_id);
        $this->assertStringStartsWith('PM-', $payment->reference);

        Http::assertSent(function (Request $request) use ($student, $payment) {
            $payload = $request->data();

            return $request->method() === 'POST'
                && $request->url() === 'https://api.paymongo.com/v2/checkout_sessions'
                && $request->hasHeader('Authorization', 'Basic ' . base64_encode('sk_test_example:'))
                && data_get($payload, 'data.attributes.line_items.0.amount') === 5000
                && data_get($payload, 'data.attributes.billing.email') === $student->email
                && data_get($payload, 'data.attributes.reference_number') === $payment->reference
                && data_get($payload, 'data.attributes.payment_method_types') === ['gcash', 'qrph', 'card'];
        });
    }

    public function test_signed_return_confirms_payment_without_double_crediting_budget(): void
    {
        $student = $this->student('return.student@example.com');
        $payment = $this->pendingPayment($student, 'cs_return_test', 'PM-RETURN-TEST');

        Http::fake([
            'https://api.paymongo.com/v1/checkout_sessions/cs_return_test' => Http::response([
                'data' => $this->paidSession('cs_return_test', 'PM-RETURN-TEST', 'pay_return_test'),
            ]),
        ]);

        $returnUrl = URL::temporarySignedRoute(
            'student.enrollment.paymongo.return',
            now()->addHour(),
            ['payment' => $payment->id]
        );

        $this->actingAs($student)->get($returnUrl)
            ->assertRedirect(route('student.enrollment.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('enrollment_payments', [
            'id' => $payment->id,
            'status' => 'paid',
            'method' => 'paymongo',
            'paymongo_payment_id' => 'pay_return_test',
            'paymongo_payment_method' => 'gcash',
        ]);
        $this->assertSame('50.00', Budget::where('title', Budget::ENROLLMENT_TITLE_PREFIX)->value('allocated_amount'));

        $this->actingAs($student)->get($returnUrl)->assertSessionHas('success');
        $this->assertSame('50.00', Budget::where('title', Budget::ENROLLMENT_TITLE_PREFIX)->value('allocated_amount'));
    }

    public function test_signed_webhook_marks_payment_paid_idempotently(): void
    {
        $student = $this->student('webhook.student@example.com');
        $payment = $this->pendingPayment($student, 'cs_webhook_test', 'PM-WEBHOOK-TEST');
        $session = $this->paidSession('cs_webhook_test', 'PM-WEBHOOK-TEST', 'pay_webhook_test');
        $rawPayload = json_encode([
            'data' => [
                'id' => 'evt_webhook_test',
                'type' => 'checkout_session.payment.paid',
                'data' => $session,
            ],
        ], JSON_UNESCAPED_SLASHES);
        $timestamp = time();
        $signature = hash_hmac('sha256', $timestamp . '.' . $rawPayload, 'whsk_test_example');
        $server = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_PAYMONGO_SIGNATURE' => "t={$timestamp},te={$signature},li=",
        ];

        $this->call('POST', route('api.paymongo.webhook'), [], [], [], $server, $rawPayload)
            ->assertOk()
            ->assertJson(['received' => true]);
        $this->call('POST', route('api.paymongo.webhook'), [], [], [], $server, $rawPayload)
            ->assertOk();

        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame('pay_webhook_test', $payment->fresh()->paymongo_payment_id);
        $this->assertSame('50.00', Budget::where('title', Budget::ENROLLMENT_TITLE_PREFIX)->value('allocated_amount'));
    }

    public function test_webhook_rejects_an_invalid_signature(): void
    {
        $student = $this->student('invalid.webhook@example.com');
        $payment = $this->pendingPayment($student, 'cs_invalid_test', 'PM-INVALID-TEST');
        $rawPayload = json_encode(['data' => ['type' => 'checkout_session.payment.paid']]);

        $this->call('POST', route('api.paymongo.webhook'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_PAYMONGO_SIGNATURE' => 't=' . time() . ',te=invalid,li=',
        ], $rawPayload)->assertUnauthorized();

        $this->assertSame('pending', $payment->fresh()->status);
        $this->assertDatabaseMissing('budgets', ['title' => Budget::ENROLLMENT_TITLE_PREFIX]);
    }

    private function student(string $email): User
    {
        return User::create([
            'fullname' => 'PayMongo Test Student',
            'email' => $email,
            'password' => bcrypt('password'),
            'role' => 'student',
            'status' => 'active',
        ]);
    }

    private function pendingPayment(User $student, string $sessionId, string $reference): EnrollmentPayment
    {
        return EnrollmentPayment::create([
            'user_id' => $student->id,
            'amount' => 50,
            'semester' => '2026-2027 - First Semester',
            'method' => 'paymongo',
            'status' => 'pending',
            'reference' => $reference,
            'proof_status' => 'pending',
            'paymongo_checkout_session_id' => $sessionId,
        ]);
    }

    private function paidSession(string $sessionId, string $reference, string $paymentId): array
    {
        return [
            'id' => $sessionId,
            'type' => 'checkout_session',
            'attributes' => [
                'reference_number' => $reference,
                'status' => 'active',
                'payments' => [[
                    'id' => $paymentId,
                    'type' => 'payment',
                    'attributes' => [
                        'amount' => 5000,
                        'currency' => 'PHP',
                        'status' => 'paid',
                        'paid_at' => now()->timestamp,
                        'source' => ['type' => 'gcash'],
                    ],
                ]],
            ],
        ];
    }
}
