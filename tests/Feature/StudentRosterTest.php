<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\StudentRoster;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class StudentRosterTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->admin = User::create([
            'fullname' => 'Site Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => 'admin',
            'status' => 'active',
        ]);
    }

    public function test_admin_adds_a_student_without_an_email(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.students.store'), [
                'year_level' => '2nd Year',
                'department' => 'BSED',
                'first_name' => 'Ana',
                'middle_name' => '',
                'last_name' => 'Reyes',
                'student_id' => '2025-0101',
                'email' => '',
            ])
            ->assertRedirect(route('admin.students.index'))
            ->assertSessionHas('success', fn ($m) => str_contains($m, 'registering with ID number 2025-0101'));

        $student = User::where('student_id', '2025-0101')->firstOrFail();
        $this->assertNull($student->email);
        $this->assertSame('Ana Reyes', $student->fullname);
        $this->assertSame('2nd Year', $student->year_level);
        $this->assertSame('BSED', $student->department);
        $this->assertSame('student', $student->role);

        $this->actingAs($this->admin)
            ->get(route('admin.students.index'))
            ->assertSee('Ana Reyes')
            ->assertSee('Not registered yet');
    }

    public function test_adding_a_duplicate_id_number_is_rejected(): void
    {
        $this->rosterStudent('2025-0101', 'Existing', 'Student');

        $this->actingAs($this->admin)
            ->post(route('admin.students.store'), [
                'year_level' => '1st Year',
                'department' => 'BSIT',
                'first_name' => 'Other',
                'last_name' => 'Person',
                'student_id' => '2025-0101',
            ])
            ->assertSessionHasErrors(['student_id' => 'A student with this ID number already exists.']);

        $this->assertSame(1, User::where('student_id', '2025-0101')->count());
    }

    public function test_csv_import_adds_valid_rows_and_reports_the_rest(): void
    {
        $this->rosterStudent('2025-0009', 'Already', 'Here');

        $csv = "\xEF\xBB\xBF" . implode("\n", [
            'School Year,Department,First Name,Middle Name,Last Name,ID Number',
            '1st Year,BSIT,Juan,Santos,Dela Cruz,2026-0001',
            '2,bsed,Maria,,Clara,2026-0002',
            'Third Year,BSBA,Jose,,Rizal,2026-0003',
            '4th Year,BSHM,Bad,,Id,26-1',
            '1st Year,NURSING,Wrong,,Department,2026-0005',
            ',,,,,',
            '1st Year,BSIT,Already,,Here,2025-0009',
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.students.import'), ['csv_file' => $this->csv($csv)])
            ->assertRedirect(route('admin.students.index'))
            ->assertSessionHas('success', fn ($m) => str_contains($m, '3 student(s) imported')
                && str_contains($m, '1 were already on file')
                && str_contains($m, '2 row(s) had problems'))
            ->assertSessionHas('import_errors', fn ($errors) => count($errors) === 2
                && str_starts_with($errors[0], 'Row 5: ID Number must use the format YYYY-XXXX')
                && str_starts_with($errors[1], 'Row 6: Department must be one of'));

        $this->assertSame('Juan Santos Dela Cruz', User::where('student_id', '2026-0001')->value('fullname'));
        $this->assertSame('2nd Year', User::where('student_id', '2026-0002')->value('year_level'));
        $this->assertSame('BSED', User::where('student_id', '2026-0002')->value('department'));
        $this->assertSame('3rd Year', User::where('student_id', '2026-0003')->value('year_level'));
        $this->assertNull(User::where('student_id', '2026-0003')->value('email'));
        $this->assertFalse(User::where('student_id', '26-1')->exists());
        $this->assertSame(1, User::where('student_id', '2025-0009')->count());
    }

    public function test_csv_import_accepts_semicolons_and_no_heading_row(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.students.import'), ['csv_file' => $this->csv("1st Year;BSIT;Ana;;Reyes;2026-0011\n")])
            ->assertSessionHas('success', fn ($m) => str_contains($m, '1 student(s) imported'));

        $this->assertSame('Ana Reyes', User::where('student_id', '2026-0011')->value('fullname'));
    }

    public function test_the_template_lists_the_columns_in_order(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.students.template'));

        $response->assertOk()->assertDownload('student_import_template.csv');
        $content = $response->streamedContent();
        $this->assertSame(StudentRoster::COLUMNS, str_getcsv(strtok($content, "\n")));

        // The template itself imports cleanly: heading skipped, example added.
        $this->actingAs($this->admin)
            ->post(route('admin.students.import'), ['csv_file' => $this->csv($content)])
            ->assertSessionHas('success', '1 student(s) imported.');
        $this->assertSame('Juan Santos Dela Cruz', User::where('student_id', '2026-0001')->value('fullname'));
    }

    public function test_registering_with_a_roster_id_claims_that_record(): void
    {
        $roster = $this->rosterStudent('2026-0001', 'Juan', 'Dela Cruz');

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->post('/register', $this->registration('2026-0001', 'DELA CRUZ'))
            ->assertSessionHasNoErrors()
            ->assertOk();

        $this->assertSame(1, User::where('student_id', '2026-0001')->count());
        $roster->refresh();
        $this->assertSame('juan.claims@gmail.com', $roster->email);
        $this->assertSame('3rd Year', $roster->year_level);
        $this->assertAuthenticatedAs($roster);
    }

    public function test_a_roster_id_cannot_be_claimed_under_another_last_name(): void
    {
        $roster = $this->rosterStudent('2026-0001', 'Juan', 'Dela Cruz');

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->post('/register', $this->registration('2026-0001', 'Santos'))
            ->assertSessionHasErrors('student_id');

        $this->assertNull($roster->fresh()->email);
        $this->assertGuest();
    }

    public function test_an_id_that_already_has_an_account_is_still_rejected(): void
    {
        User::create([
            'fullname' => 'Registered Student', 'last_name' => 'Dela Cruz', 'student_id' => '2026-0001',
            'email' => 'registered@gmail.com', 'password' => 'Password123', 'role' => 'student', 'status' => 'active',
        ]);

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->post('/register', $this->registration('2026-0001', 'Dela Cruz'))
            ->assertSessionHasErrors('student_id');

        $this->assertSame(1, User::where('student_id', '2026-0001')->count());
    }

    public function test_year_levels_and_names_are_matched_leniently(): void
    {
        foreach (['1st Year' => '1st Year', '1' => '1st Year', '2nd' => '2nd Year', 'third year' => '3rd Year', 'YEAR 4' => '4th Year', 'Fourth' => '4th Year'] as $input => $expected) {
            $this->assertSame($expected, StudentRoster::normalizeYearLevel($input), $input);
        }
        $this->assertNull(StudentRoster::normalizeYearLevel('5th Year'));
        $this->assertNull(StudentRoster::normalizeYearLevel(''));

        $this->assertTrue(StudentRoster::namesMatch('Dela Cruz', 'DELA-CRUZ'));
        $this->assertTrue(StudentRoster::namesMatch('Salvaña', 'salvana'));
        $this->assertFalse(StudentRoster::namesMatch('Dela Cruz', 'Santos'));
        $this->assertFalse(StudentRoster::namesMatch('', ''));
    }

    private function rosterStudent(string $studentId, string $first, string $last): User
    {
        return StudentRoster::add([
            'first_name' => $first, 'middle_name' => null, 'last_name' => $last,
            'student_id' => $studentId, 'department' => 'BSIT', 'year_level' => '1st Year',
        ]);
    }

    private function registration(string $studentId, string $lastName): array
    {
        return [
            'first_name' => 'Juan',
            'last_name' => $lastName,
            'dob' => now()->subYears(20)->toDateString(),
            'year_level' => '3rd Year',
            'department' => 'BSIT',
            'student_id' => $studentId,
            'email' => 'juan.claims@gmail.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ];
    }

    private function csv(string $contents): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('roster.csv', $contents);
    }
}
