<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PortalNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_staff_portal_can_load_the_shared_notification_feed(): void
    {
        $users = collect(['admin', 'officer', 'treasurer', 'dean'])
            ->mapWithKeys(fn (string $role) => [$role => $this->createUser($role)]);

        DB::table('announcements')->insert([
            'title' => 'Live portal update',
            'content' => 'This announcement should appear in every portal notification bell.',
            'created_by' => $users['admin']->id,
            'created_at' => now(),
        ]);

        foreach ($users as $role => $user) {
            $response = $this
                ->actingAs($user)
                ->getJson('http://' . $role . '.mccsupremestudentcouncil.com/notifications');

            $response
                ->assertOk()
                ->assertJsonPath('0.kind', 'announcement')
                ->assertJsonPath('0.title', 'Live portal update');
        }
    }

    public function test_opening_the_staff_bell_marks_updates_as_seen(): void
    {
        $admin = $this->createUser('admin');

        DB::table('announcements')->insert([
            'title' => 'Unread update',
            'content' => 'Unread notification test.',
            'created_by' => $admin->id,
            'created_at' => now(),
        ]);

        $this->actingAs($admin)
            ->getJson('http://admin.mccsupremestudentcouncil.com/notifications/unread-count')
            ->assertOk()
            ->assertJson(['unread' => 1]);

        $this->actingAs($admin)
            ->postJson('http://admin.mccsupremestudentcouncil.com/notifications/read')
            ->assertOk()
            ->assertJson(['unread' => 0]);

        $this->assertNotNull($admin->fresh()->notifications_seen_at);
    }

    public function test_every_desktop_portal_renders_the_bell_and_live_update_region(): void
    {
        $portals = [
            'admin' => 'http://admin.mccsupremestudentcouncil.com/dashboard',
            'officer' => 'http://officer.mccsupremestudentcouncil.com/dashboard',
            'treasurer' => 'http://treasurer.mccsupremestudentcouncil.com/dashboard',
            'dean' => 'http://dean.mccsupremestudentcouncil.com/dashboard',
            'student' => 'http://mccsupremestudentcouncil.com/student',
        ];

        foreach ($portals as $role => $url) {
            $host = parse_url($url, PHP_URL_HOST);
            Route::domain($host)
                ->middleware('web')
                ->get('/_test-portal-shell-' . $role, fn () => view('layouts.app'))
                ->name($role . '._test.portal-shell');

            $response = $this
                ->actingAs($this->createUser($role))
                ->get('http://' . $host . '/_test-portal-shell-' . $role);

            $response
                ->assertOk()
                ->assertSee('id="sscBellBtn"', false)
                ->assertSee('data-ssc-live-region', false)
                ->assertSee('assets/js/live-updates.js', false);
        }
    }

    private function createUser(string $role): User
    {
        return User::create([
            'fullname' => ucfirst($role) . ' Test User',
            'email' => $role . '@example.test',
            'password' => Hash::make('password'),
            'role' => $role,
            'status' => 'active',
        ]);
    }
}
