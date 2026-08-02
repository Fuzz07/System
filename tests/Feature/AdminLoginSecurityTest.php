<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminLoginSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        
        // Flush cache to clear any rate limits
        Cache::flush();

        // Create a test administrator
        $this->adminUser = User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'fullname' => 'Admin User',
            'email' => 'admin@mcclawis.edu.ph',
            'password' => 'Password123', // User model hashed automatically by casts
            'role' => 'admin',
            'status' => 'active',
        ]);
    }

    /**
     * Test first-time admin login prompts for OTP and registers the device.
     */
    public function test_first_time_admin_login_prompts_for_otp_and_registers_device(): void
    {
        Mail::fake();

        // 1. Send the login request with valid credentials and PH coordinates
        $response = $this->post(route('login.submit'), [
            'email' => 'admin@mcclawis.edu.ph',
            'password' => 'Password123',
            'portal' => 'admin',
            'latitude' => 12.0,
            'longitude' => 121.0,
            'captcha_verified_token' => 'MOCK_CAPTCHA_VALID', // Mocked or verified bypass
        ]);

        // Should redirect to the main domain OTP page as we are requesting on the main domain in this test
        $response->assertRedirect(route('admin.login.otp.main'));
        $response->assertSessionHas('admin_login_otp');
        $response->assertSessionHas('admin_login_user_id', $this->adminUser->id);

        $otp = session('admin_login_otp');
        $this->assertNotNull($otp);
        $this->assertEquals(6, strlen($otp));

        // 2. Submit the OTP code
        $otpResponse = $this->withSession([
            'admin_login_otp' => $otp,
            'admin_login_otp_expires_at' => now()->addMinutes(10),
            'admin_login_user_id' => $this->adminUser->id,
        ])->post(route('admin.login.otp.submit.main'), [
            'otp' => $otp,
        ]);

        // Should successfully log in and redirect to dashboard
        $otpResponse->assertRedirect(route('admin.dashboard'));
        $this->assertTrue(Auth::check());
        $this->assertEquals($this->adminUser->id, Auth::id());

        // Refresh the user model from database
        $this->adminUser->refresh();
        $this->assertNotEmpty($this->adminUser->admin_device_token);

        // Verify a forever cookie was set with the device token
        $otpResponse->assertCookie('admin_device_token', $this->adminUser->admin_device_token);
    }

    /**
     * Test subsequent logins from the registered device succeed via OTP.
     */
    public function test_subsequent_login_from_registered_device_succeeds(): void
    {
        Mail::fake();

        // Register a device token for this admin
        $token = Str::random(60);
        $this->adminUser->update(['admin_device_token' => $token]);

        // Submit login request WITH the matching cookie
        $response = $this->withCookie('admin_device_token', $token)
            ->post(route('login.submit'), [
                'email' => 'admin@mcclawis.edu.ph',
                'password' => 'Password123',
                'portal' => 'admin',
                'latitude' => 12.0,
                'longitude' => 121.0,
                'captcha_verified_token' => 'MOCK_CAPTCHA_VALID',
            ]);

        // Device is recognized, should proceed to OTP page
        $response->assertRedirect(route('admin.login.otp.main'));
        $this->assertTrue(session()->has('admin_login_otp'));
    }

    /**
     * Test logins from an unregistered device are blocked completely.
     */
    public function test_login_from_unregistered_device_is_blocked(): void
    {
        Mail::fake();

        // Register a device token for this admin
        $token = Str::random(60);
        $this->adminUser->update(['admin_device_token' => $token]);

        // Submit login request WITHOUT the cookie (unrecognized device)
        $response = $this->post(route('login.submit'), [
            'email' => 'admin@mcclawis.edu.ph',
            'password' => 'Password123',
            'portal' => 'admin',
            'latitude' => 12.0,
            'longitude' => 121.0,
            'captcha_verified_token' => 'MOCK_CAPTCHA_VALID',
        ]);

        // Should block the login and show unrecognized device error
        $response->assertSessionHasErrors(['email']);
        $this->assertFalse(Auth::check());

        // Verify the exact error message
        $errors = session('errors');
        $this->assertStringContainsString('Access Denied: Unrecognized device', $errors->first('email'));
    }

    /**
     * Test that resetting registered device from settings clears token and allows new device lock.
     */
    public function test_reset_registered_device_clears_token_and_allows_re_registration(): void
    {
        $token = Str::random(60);
        $this->adminUser->update(['admin_device_token' => $token]);

        // Log the admin in
        $this->actingAs($this->adminUser);

        // Send reset device request
        $response = $this->post(route('admin.settings.reset_device'));

        $response->assertRedirect(route('admin.settings'));
        $response->assertSessionHas('success');

        // Verify DB token is cleared
        $this->adminUser->refresh();
        $this->assertNull($this->adminUser->admin_device_token);
    }
}
