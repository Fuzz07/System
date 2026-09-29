<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationOtpTest extends TestCase
{
    use RefreshDatabase;

    private string $email = 'otp.student@gmail.com';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        Cache::flush();
        Mail::fake();
        Http::fake(['*' => Http::response(['IfExistsResult' => 0])]); // do not reach out to Microsoft during tests
        config(['ssc.enforce_eligibility_whitelist' => false]);
    }

    private function startVerification(): void
    {
        $this->postJson('/register/check-email', ['email' => $this->email])
            ->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonStructure(['expires_at', 'resend_available_in']);
    }

    public function test_wrong_code_reports_remaining_attempts(): void
    {
        $this->startVerification();

        $response = $this->postJson('/register/verify-otp', [
            'email' => $this->email,
            'otp' => '000000',
        ]);

        $response->assertOk()->assertJson(['success' => false, 'attempts_left' => 4]);
        $this->assertStringContainsString('Incorrect verification code', $response->json('message'));
    }

    public function test_code_is_disabled_after_five_wrong_attempts(): void
    {
        $this->startVerification();

        for ($i = 0; $i < 4; $i++) {
            $this->postJson('/register/verify-otp', ['email' => $this->email, 'otp' => '000000'])
                ->assertJson(['success' => false]);
        }

        $final = $this->postJson('/register/verify-otp', ['email' => $this->email, 'otp' => '000000']);
        $final->assertOk()->assertJson([
            'success' => false,
            'locked' => true,
            'can_resend' => true,
        ]);

        $this->assertNull(session('register_otp'));
    }

    public function test_correct_code_verifies_and_consumes_the_otp(): void
    {
        $this->startVerification();
        $otp = session('register_otp');

        $this->postJson('/register/verify-otp', ['email' => $this->email, 'otp' => $otp])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertTrue(session('register_email_verified'));
        $this->assertNull(session('register_otp'));
    }

    public function test_sending_a_code_to_another_email_clears_the_earlier_verification(): void
    {
        $this->startVerification();
        $this->postJson('/register/verify-otp', ['email' => $this->email, 'otp' => session('register_otp')])
            ->assertJson(['success' => true]);

        $this->postJson('/register/check-email', ['email' => 'other.student@gmail.com'])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertNull(session('register_email_verified'));
        $this->assertSame('other.student@gmail.com', session('register_email'));
    }

    public function test_registration_page_renders_clean_direct_steps(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSeeInOrder(['Student details', 'Account security'])
            ->assertSee('id="step-details"', false)
            ->assertSee('id="step-2"', false);
    }

    public function test_failed_submit_retains_old_input(): void
    {
        // A taken student ID sends the student back with their input.
        \App\Models\User::create([
            'fullname' => 'Existing Student',
            'email' => 'existing@gmail.com',
            'student_id' => '2024-0001',
            'password' => 'Password123',
            'role' => 'student',
            'status' => 'active',
        ]);

        $this->from('/register')->post('/register', [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'dob' => now()->subYears(20)->toDateString(),
            'year_level' => '2nd Year',
            'department' => 'BSIT',
            'student_id' => '2024-0001',
            'email' => $this->email,
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ])->assertRedirect('/register')->assertSessionHasErrors('student_id');

        $this->get('/register')
            ->assertOk()
            ->assertSee('value="Jane"', false);
    }

    public function test_expired_code_is_rejected_but_email_is_kept_for_resend(): void
    {
        $this->startVerification();
        $otp = session('register_otp');
        session(['register_otp_expires_at' => now()->subMinute()]);

        $this->postJson('/register/verify-otp', ['email' => $this->email, 'otp' => $otp])
            ->assertOk()
            ->assertJson(['success' => false, 'expired' => true, 'can_resend' => true]);

        $this->assertSame($this->email, session('register_email'));
    }

    public function test_resend_is_blocked_during_the_cooldown(): void
    {
        $this->startVerification();

        $response = $this->postJson('/register/resend-otp', ['email' => $this->email]);

        $response->assertOk()->assertJson(['success' => false]);
        $this->assertStringContainsString('Please wait', $response->json('message'));
    }

    public function test_resend_issues_a_new_code_after_the_cooldown(): void
    {
        $this->startVerification();
        $firstOtp = session('register_otp');
        session(['register_otp_last_sent_at' => now()->subSeconds(120)]);

        $response = $this->postJson('/register/resend-otp', ['email' => $this->email]);

        $response->assertOk()->assertJson(['success' => true, 'resends_left' => 2]);
        $this->assertNotSame($firstOtp, session('register_otp'));
        $this->assertSame(0, (int) session('register_otp_attempts'));
    }

    public function test_resend_stops_after_the_maximum_number_of_codes(): void
    {
        $this->startVerification();

        for ($i = 0; $i < 3; $i++) {
            session(['register_otp_last_sent_at' => now()->subSeconds(120)]);
            $this->postJson('/register/resend-otp', ['email' => $this->email])
                ->assertJson(['success' => true]);
        }

        session(['register_otp_last_sent_at' => now()->subSeconds(120)]);
        $this->postJson('/register/resend-otp', ['email' => $this->email])
            ->assertOk()
            ->assertJson(['success' => false, 'restart' => true]);
    }

    public function test_resend_for_a_different_email_is_rejected(): void
    {
        $this->startVerification();

        $this->postJson('/register/resend-otp', ['email' => 'someone.else@gmail.com'])
            ->assertOk()
            ->assertJson(['success' => false, 'restart' => true]);
    }
}
