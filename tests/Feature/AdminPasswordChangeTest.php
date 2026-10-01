<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminPasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    private const CURRENT = 'OldPassw0rd!';
    private const NEW = 'N3w-Secure#Pass';

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::create([
            'fullname' => 'Site Admin',
            'email' => 'admin@example.com',
            'password' => self::CURRENT,
            'role' => 'admin',
            'status' => 'active',
            'remember_token' => 'old-remember-token',
        ]);
    }

    public function test_settings_page_shows_the_change_password_form(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.settings'))
            ->assertOk()
            ->assertSee('Change Password')
            ->assertSee('action="' . route('admin.settings.password') . '"', false)
            ->assertSee('autocomplete="current-password"', false);
    }

    public function test_admin_can_change_their_password_and_other_devices_are_signed_out(): void
    {
        $other = User::create(['fullname' => 'Officer', 'email' => 'officer@example.com', 'password' => 'secret123', 'role' => 'officer', 'status' => 'active']);
        $this->signedInSession('admin-laptop', $this->admin);
        $this->signedInSession('officer-phone', $other);

        $this->actingAs($this->admin)
            ->put(route('admin.settings.password'), $this->form())
            ->assertRedirect(route('admin.settings'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->admin->refresh();
        $this->assertTrue(Hash::check(self::NEW, $this->admin->password));
        $this->assertNotSame('old-remember-token', $this->admin->getRememberToken());
        $this->assertAuthenticatedAs($this->admin);

        $this->assertDatabaseMissing('sessions', ['id' => 'admin-laptop']);
        $this->assertDatabaseHas('sessions', ['id' => 'officer-phone']);
        $this->assertDatabaseHas('activity_logs', ['user_id' => $this->admin->id, 'action' => 'ADMIN_PASSWORD_CHANGE']);
    }

    /**
     * @dataProvider rejectedChanges
     */
    public function test_password_change_is_refused(string $field, array $overrides): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.settings'))
            ->put(route('admin.settings.password'), $overrides + $this->form())
            ->assertRedirect(route('admin.settings'))
            ->assertSessionHasErrors($field);

        $this->assertTrue(Hash::check(self::CURRENT, $this->admin->fresh()->password));
    }

    public static function rejectedChanges(): array
    {
        return [
            'wrong current password' => ['current_password', ['current_password' => 'not-my-password']],
            'missing current password' => ['current_password', ['current_password' => '']],
            'too short' => ['password', ['password' => 'Sh0rt!', 'password_confirmation' => 'Sh0rt!']],
            'no uppercase' => ['password', ['password' => 'lowercase-only-1', 'password_confirmation' => 'lowercase-only-1']],
            'no number' => ['password', ['password' => 'No-Numbers-Here', 'password_confirmation' => 'No-Numbers-Here']],
            'no symbol' => ['password', ['password' => 'NoSymbols123', 'password_confirmation' => 'NoSymbols123']],
            'confirmation mismatch' => ['password', ['password_confirmation' => 'Something-Else-9']],
            'same as current' => ['password', ['password' => self::CURRENT, 'password_confirmation' => self::CURRENT]],
        ];
    }

    public function test_only_admins_can_change_the_admin_password(): void
    {
        $officer = User::create(['fullname' => 'Officer', 'email' => 'officer@example.com', 'password' => self::CURRENT, 'role' => 'officer', 'status' => 'active']);

        $response = $this->actingAs($officer)->put(route('admin.settings.password'), $this->form());

        $this->assertContains($response->status(), [302, 403]);
        $this->assertTrue(Hash::check(self::CURRENT, $officer->fresh()->password));
    }

    public function test_repeated_attempts_are_throttled(): void
    {
        foreach (range(1, 5) as $attempt) {
            $this->actingAs($this->admin)
                ->put(route('admin.settings.password'), ['current_password' => 'guess-' . $attempt] + $this->form());
        }

        $this->actingAs($this->admin)
            ->put(route('admin.settings.password'), $this->form())
            ->assertStatus(429);

        $this->assertTrue(Hash::check(self::CURRENT, $this->admin->fresh()->password));
    }

    private function form(): array
    {
        return [
            'current_password' => self::CURRENT,
            'password' => self::NEW,
            'password_confirmation' => self::NEW,
        ];
    }

    private function signedInSession(string $id, User $user): void
    {
        DB::table('sessions')->insert([
            'id' => $id,
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Browser',
            'payload' => '',
            'last_activity' => now()->timestamp,
        ]);
    }
}
