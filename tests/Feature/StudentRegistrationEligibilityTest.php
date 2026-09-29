<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class StudentRegistrationEligibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        Cache::flush();
        Mail::fake();
    }

    /**
     * Test that non-gmail email fails validation on check-email.
     */
    public function test_non_gmail_email_fails_check_email(): void
    {
        $response = $this->postJson('/register/check-email', [
            'email' => 'student@yahoo.com',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'code' => 'invalid_email',
        ]);
    }

    /**
     * Test that any valid Gmail directly passes check-email and sends OTP without eligibility whitelist restrictions.
     */
    public function test_gmail_account_directly_passes_check_email_without_whitelist(): void
    {
        $response = $this->postJson('/register/check-email', [
            'email' => 'new.student@gmail.com',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $this->assertNotNull(session('register_otp'));
    }

    /**
     * Test that an already registered Gmail account returns a clear conflict response.
     */
    public function test_registered_gmail_account_returns_a_clear_error(): void
    {
        User::create([
            'fullname' => 'Registered Student',
            'email' => 'registered.student@gmail.com',
            'password' => 'Password123',
            'role' => 'student',
            'status' => 'active',
        ]);

        $response = $this->postJson('/register/check-email', [
            'email' => 'REGISTERED.STUDENT@GMAIL.COM',
        ]);

        $response->assertConflict()->assertJson([
            'success' => false,
            'code' => 'already_registered',
            'message' => 'This Gmail account is already registered. Please sign in or use Forgot Password to regain access.',
        ]);
    }

    /**
     * Test that direct registration with verified Gmail activates account immediately and logs in.
     */
    public function test_registration_with_verified_gmail_activates_account_and_logs_in(): void
    {
        $email = 'fresh.student@gmail.com';

        // Simulate successful OTP verification in session
        session([
            'register_email' => $email,
            'register_email_verified' => true,
        ]);

        $response = $this->post('/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'dob' => now()->subYears(20)->toDateString(),
            'year_level' => '3rd Year',
            'department' => 'BSIT',
            'student_id' => '2023-0001',
            'email' => $email,
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $response->assertOk();
        $response->assertViewIs('auth.confirm-success');

        $user = User::where('email', $email)->first();
        $this->assertNotNull($user);
        $this->assertSame('active', $user->status);
        $this->assertAuthenticatedAs($user);
    }

    public function test_registration_page_renders_the_guided_flow_and_visual(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('registration-shell', false)
            ->assertSee('Student details')
            ->assertSee('Email verification')
            ->assertSee('registration-visual-v1.jpg', false);
    }
}
