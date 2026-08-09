<?php

namespace Tests\Feature;

use App\Models\EligibleStudent;
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
     * Test that an email not on the eligible students list returns a clear error message via AJAX check-email.
     */
    public function test_ineligible_email_returns_error_response_in_check_email(): void
    {
        config(['ssc.enforce_eligibility_whitelist' => true]);

        $response = $this->postJson('/register/check-email', [
            'email' => 'unlisted.student@mcclawis.edu.ph',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => false,
            'message' => 'This Microsoft 365 account is not eligible to register. Please contact the SSC admin to have your account added to the eligible list.',
        ]);
    }

    /**
     * Test that an eligible student email passes the check-email endpoint.
     */
    public function test_eligible_email_passes_check_email(): void
    {
        config(['ssc.enforce_eligibility_whitelist' => true]);

        EligibleStudent::create([
            'email' => 'eligible.student@mcclawis.edu.ph',
            'student_name' => 'Eligible Student',
            'department' => 'BSIT',
            'year_level' => '3rd Year',
        ]);

        $response = $this->postJson('/register/check-email', [
            'email' => 'eligible.student@mcclawis.edu.ph',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }

    /**
     * Test that direct submission to /register with an ineligible email fails validation.
     */
    public function test_ineligible_email_fails_validation_on_direct_register_submit(): void
    {
        config(['ssc.enforce_eligibility_whitelist' => true]);

        $response = $this->post('/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'age' => 20,
            'year_level' => '3rd Year',
            'department' => 'BSIT',
            'student_id' => '2023-0001',
            'email' => 'unlisted.student@mcclawis.edu.ph',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'This Microsoft 365 account is not eligible to register. Please contact the SSC admin to have your account added to the eligible list.',
        ]);
    }
}
