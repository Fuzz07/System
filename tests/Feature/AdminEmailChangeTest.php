<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminEmailChangeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'fullname' => 'Site Admin',
            'email' => 'admin@example.com',
            'password' => 'OldPassw0rd!',
            'role' => 'admin',
            'status' => 'active',
        ]);
    }

    public function test_admin_settings_show_the_gmail_change_verification_form(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.settings'))
            ->assertOk()
            ->assertSee('Change Admin Gmail')
            ->assertSee('Send Verification Code')
            ->assertSee('New Gmail address');

        $this->withSession($this->pendingChange())
            ->get(route('admin.settings'))
            ->assertOk()
            ->assertSee('Verify and Change Gmail')
            ->assertSee('Six-digit code sent to admin.new@gmail.com');
    }

    public function test_admin_must_verify_the_new_gmail_before_it_replaces_the_account_email(): void
    {
        Mail::shouldReceive('send')->once();

        $this->actingAs($this->admin)
            ->post(route('admin.settings.email.request_otp'), ['new_email' => '  ADMIN.NEW@gmail.com  '])
            ->assertRedirect(route('admin.settings'))
            ->assertSessionHas('admin_email_change_address', 'admin.new@gmail.com')
            ->assertSessionHas('admin_email_change_user_id', $this->admin->id);

        $this->assertSame('admin@example.com', $this->admin->fresh()->email);
        $this->assertNotSame('123456', session('admin_email_change_otp_hash'));

        $this->withSession([
            'admin_email_change_user_id' => $this->admin->id,
            'admin_email_change_address' => 'admin.new@gmail.com',
            'admin_email_change_otp_hash' => Hash::make('123456'),
            'admin_email_change_expires_at' => now()->addMinutes(5)->timestamp,
            'admin_email_change_attempts' => 0,
        ])->post(route('admin.settings.email.verify'), [
            'new_email' => 'admin.new@gmail.com',
            'otp' => '123456',
        ])
            ->assertRedirect(route('admin.settings'))
            ->assertSessionHas('success');

        $this->assertSame('admin.new@gmail.com', $this->admin->fresh()->email);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'ADMIN_EMAIL_CHANGE',
        ]);
    }

    public function test_invalid_or_expired_codes_do_not_change_the_email(): void
    {
        $this->actingAs($this->admin)
            ->withSession($this->pendingChange())
            ->from(route('admin.settings'))
            ->post(route('admin.settings.email.verify'), [
                'new_email' => 'admin.new@gmail.com',
                'otp' => '000000',
            ])
            ->assertRedirect(route('admin.settings'))
            ->assertSessionHasErrors('otp');

        $this->assertSame('admin@example.com', $this->admin->fresh()->email);

        $this->withSession($this->pendingChange(['admin_email_change_expires_at' => now()->subMinute()->timestamp]))
            ->post(route('admin.settings.email.verify'), [
                'new_email' => 'admin.new@gmail.com',
                'otp' => '123456',
            ])
            ->assertRedirect(route('admin.settings'))
            ->assertSessionHas('danger');

        $this->assertSame('admin@example.com', $this->admin->fresh()->email);
    }

    public function test_duplicate_and_non_gmail_addresses_are_rejected(): void
    {
        User::create([
            'fullname' => 'Other Admin',
            'email' => 'taken@gmail.com',
            'password' => 'OldPassw0rd!',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.settings.email.request_otp'), ['new_email' => 'not-gmail@example.com'])
            ->assertSessionHasErrors('new_email');

        $this->post(route('admin.settings.email.request_otp'), ['new_email' => 'taken@gmail.com'])
            ->assertSessionHasErrors('new_email');

        $this->assertSame('admin@example.com', $this->admin->fresh()->email);
    }

    public function test_too_many_incorrect_codes_invalidate_the_pending_change(): void
    {
        $session = $this->pendingChange(['admin_email_change_attempts' => 4]);

        $this->actingAs($this->admin)
            ->withSession($session)
            ->post(route('admin.settings.email.verify'), [
                'new_email' => 'admin.new@gmail.com',
                'otp' => '000000',
            ])
            ->assertRedirect(route('admin.settings'))
            ->assertSessionHas('danger')
            ->assertSessionMissing('admin_email_change_otp_hash');

        $this->assertSame('admin@example.com', $this->admin->fresh()->email);
    }

    /** @return array<string, mixed> */
    private function pendingChange(array $overrides = []): array
    {
        return array_replace([
            'admin_email_change_user_id' => $this->admin->id,
            'admin_email_change_address' => 'admin.new@gmail.com',
            'admin_email_change_otp_hash' => Hash::make('123456'),
            'admin_email_change_expires_at' => now()->addMinutes(5)->timestamp,
            'admin_email_change_attempts' => 0,
        ], $overrides);
    }
}
