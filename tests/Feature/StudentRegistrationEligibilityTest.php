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
     * Test that non-gmail email fails validation on registration.
     */
    public function test_non_gmail_email_fails_registration(): void
    {
        $response = $this->post('/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'dob' => now()->subYears(20)->toDateString(),
            'year_level' => '3rd Year',
            'department' => 'BSIT',
            'student_id' => '2023-0001',
            'email' => 'student@yahoo.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Test that an already registered Gmail account returns a validation error.
     */
    public function test_registered_gmail_account_returns_a_validation_error(): void
    {
        User::create([
            'fullname' => 'Registered Student',
            'email' => 'registered.student@gmail.com',
            'password' => 'Password123',
            'role' => 'student',
            'status' => 'active',
        ]);

        $response = $this->post('/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'dob' => now()->subYears(20)->toDateString(),
            'year_level' => '3rd Year',
            'department' => 'BSIT',
            'student_id' => '2023-0002',
            'email' => 'registered.student@gmail.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Test that direct registration with Gmail activates account immediately and logs in without OTP.
     */
    public function test_registration_with_gmail_activates_account_and_logs_in(): void
    {
        $email = 'fresh.student@gmail.com';

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

    /**
     * Test that registrants must be at least 17, counted to the day.
     */
    public function test_registrants_must_be_at_least_17_years_old(): void
    {
        $details = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'year_level' => '1st Year',
            'department' => 'BSIT',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ];

        // One day short of turning 17.
        $this->post('/register', $details + [
            'dob' => now()->subYears(17)->addDay()->toDateString(),
            'student_id' => '2026-0001',
            'email' => 'too.young@gmail.com',
        ])->assertSessionHasErrors(['dob' => 'You must be at least 17 years old to register.']);

        $this->assertDatabaseMissing('users', ['email' => 'too.young@gmail.com']);

        // Turning 17 today.
        $this->post('/register', $details + [
            'dob' => now()->subYears(17)->toDateString(),
            'student_id' => '2026-0002',
            'email' => 'just.seventeen@gmail.com',
        ])->assertSessionHasNoErrors();

        $registered = User::where('email', 'just.seventeen@gmail.com')->first();
        $this->assertSame(17, $registered->age);
        $this->assertSame(now()->subYears(17)->toDateString(), $registered->birthdate->toDateString());
    }

    /**
     * Test that the registration page renders the two-step flow and visuals.
     */
    public function test_registration_page_renders_the_guided_flow_and_visual(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('registration-shell', false)
            ->assertSee('Student details')
            ->assertSee('Account security')
            ->assertSee('registration-visual-v1.jpg', false);
    }

    /**
     * Test Google OAuth redirect route is available.
     */
    public function test_google_oauth_redirect_initiates(): void
    {
        config(['services.google.client_id' => 'test-client-id']);
        config(['services.google.client_secret' => 'test-client-secret']);

        $response = $this->get('/auth/google');
        $response->assertRedirect();
        $this->assertStringContainsString('accounts.google.com', $response->headers->get('Location'));
    }
}
