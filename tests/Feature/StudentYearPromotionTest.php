<?php

namespace Tests\Feature;

use App\Models\SchoolYear;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentYearPromotionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private SchoolYear $current;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->user('admin', 'Site Admin', null);

        // The state right after the migration: the active year is the baseline.
        $this->current = $this->schoolYear('2025-2026', active: true, promoted: true);
    }

    public function test_activating_the_next_school_year_promotes_everyone_and_archives_4th_years(): void
    {
        $first = $this->user('student', 'First Year', '1st Year');
        $second = $this->user('student', 'Second Year', '2nd Year');
        $third = $this->user('student', 'Third Year', '3rd Year');
        $fourth = $this->user('student', 'Fourth Year', '4th Year');
        $pending = $this->user('student', 'Pending Second', '2nd Year', 'inactive');
        $officer = $this->user('officer', 'Officer Fourth', '4th Year');
        $next = $this->schoolYear('2026-2027');

        $this->activate($next)
            ->assertSessionHas('success', fn ($message) => str_contains($message, '4 student(s) moved up a year level')
                && str_contains($message, '1 graduating student(s) were archived'));

        $this->assertLevel($first, '2nd Year');
        $this->assertLevel($second, '3rd Year');
        $this->assertLevel($third, '4th Year');
        $this->assertLevel($pending, '3rd Year', 'inactive');

        $fourth->refresh();
        $this->assertSame('Graduated', $fourth->year_level);
        $this->assertSame('inactive', $fourth->status);
        $this->assertNotNull($fourth->archived_at);
        $this->assertSame('2025-2026', $fourth->graduated_school_year);

        // Officer accounts are staff logins and are left alone.
        $this->assertLevel($officer, '4th Year');
        $this->assertNull($officer->fresh()->archived_at);
    }

    public function test_switching_semester_or_going_back_a_year_never_promotes_twice(): void
    {
        $student = $this->user('student', 'Steady Student', '2nd Year');
        $next = $this->schoolYear('2026-2027');

        $this->activate($next);
        $this->assertLevel($student, '3rd Year');

        $this->activate($next, SchoolYear::SEMESTER_SECOND);
        $this->activate($this->current);
        $this->activate($next);

        $this->assertLevel($student, '3rd Year');
    }

    public function test_skipping_a_school_year_promotes_by_the_gap(): void
    {
        $first = $this->user('student', 'First Year', '1st Year');
        $third = $this->user('student', 'Third Year', '3rd Year');
        $fourth = $this->user('student', 'Fourth Year', '4th Year');

        $this->activate($this->schoolYear('2027-2028'));

        $this->assertLevel($first, '3rd Year');
        $this->assertSame('2026-2027', $third->fresh()->graduated_school_year);
        $this->assertSame('2025-2026', $fourth->fresh()->graduated_school_year);
    }

    public function test_the_first_tracked_school_year_only_becomes_the_starting_point(): void
    {
        $this->current->forceFill(['students_promoted_at' => null])->save();
        $student = $this->user('student', 'Fresh Install', '1st Year');

        $this->activate($this->schoolYear('2026-2027'));
        $this->assertLevel($student, '1st Year');

        $this->activate($this->schoolYear('2027-2028'));
        $this->assertLevel($student, '2nd Year');
    }

    public function test_settings_warns_before_an_activation_that_promotes_students(): void
    {
        $this->schoolYear('2026-2027');

        $this->actingAs($this->admin)
            ->get(route('admin.settings'))
            ->assertOk()
            ->assertSee('Activating 2026-2027 moves every student up 1 year level.', false)
            ->assertDontSee('Activating 2025-2026 moves', false);
    }

    public function test_archived_students_leave_the_roster_and_can_be_restored(): void
    {
        $this->user('student', 'Awaiting Approval', '1st Year', 'inactive');
        $graduate = $this->user('student', 'Recent Graduate', '4th Year');
        $this->activate($this->schoolYear('2026-2027'));

        $this->actingAs($this->admin)
            ->get(route('admin.students.index'))
            ->assertOk()
            ->assertSee('Pending: 1')
            ->assertSee('Archived: 1')
            ->assertSee('Awaiting Approval')
            ->assertDontSee('Recent Graduate');

        $this->actingAs($this->admin)
            ->get(route('admin.students.index', ['view' => 'archived']))
            ->assertOk()
            ->assertSee('Recent Graduate')
            ->assertSee('SY 2025-2026')
            ->assertDontSee('Awaiting Approval');

        $this->actingAs($this->admin)
            ->patch(route('admin.students.restore', $graduate))
            ->assertSessionHas('success');

        $graduate->refresh();
        $this->assertSame('4th Year', $graduate->year_level);
        $this->assertSame('active', $graduate->status);
        $this->assertNull($graduate->archived_at);
        $this->assertNull($graduate->graduated_school_year);
    }

    public function test_an_archived_students_open_session_is_closed(): void
    {
        $graduate = $this->user('student', 'Logged In Graduate', '4th Year');
        $this->actingAs($graduate);

        // Staying signed in across the school-year change.
        $this->activate($this->schoolYear('2026-2027'));

        $this->actingAs($graduate->fresh())
            ->get(route('student.announcements'))
            ->assertRedirect(route('login.student'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    private function activate(SchoolYear $schoolYear, string $semester = SchoolYear::SEMESTER_FIRST)
    {
        return $this->actingAs($this->admin)
            ->patch(route('admin.settings.sy.activate', $schoolYear), ['semester' => $semester])
            ->assertRedirect(route('admin.settings'));
    }

    private function assertLevel(User $user, string $level, string $status = 'active'): void
    {
        $user->refresh();
        $this->assertSame($level, $user->year_level, "{$user->fullname} year level");
        $this->assertSame($status, $user->status, "{$user->fullname} status");
    }

    private function schoolYear(string $label, bool $active = false, bool $promoted = false): SchoolYear
    {
        $schoolYear = SchoolYear::create([
            'label' => $label,
            'semester' => SchoolYear::SEMESTER_FIRST,
            'is_active' => $active,
        ]);

        if ($promoted) {
            $schoolYear->forceFill(['students_promoted_at' => now()])->save();
        }

        return $schoolYear;
    }

    private function user(string $role, string $name, ?string $yearLevel, string $status = 'active'): User
    {
        return User::create([
            'fullname' => $name,
            'email' => str_replace(' ', '.', strtolower($name)) . '@example.com',
            'password' => 'password',
            'role' => $role,
            'status' => $status,
            'year_level' => $yearLevel,
            'department' => 'BSIT',
        ]);
    }
}
