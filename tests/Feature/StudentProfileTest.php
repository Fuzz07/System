<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_open_account_editor_on_web_and_mobile(): void
    {
        $student = $this->student();

        $this->actingAs($student)
            ->get(route('student.profile.edit'))
            ->assertOk()
            ->assertViewIs('student.profile')
            ->assertSee('Save account details');

        $this->actingAs($student)
            ->get(route('mobile.student.profile.edit'))
            ->assertOk()
            ->assertViewIs('mobile.student.profile')
            ->assertSee('Save account details');
    }

    public function test_student_can_update_personal_and_academic_details(): void
    {
        $student = $this->student();

        $this->actingAs($student)
            ->put(route('student.profile.update'), $this->details([
                'first_name' => 'Maria',
                'middle_name' => 'Santos',
                'last_name' => 'Reyes',
                'birthdate' => now()->subYears(21)->toDateString(),
                'year_level' => '4th Year',
                'department' => 'BSBA',
                'student_id' => '2022-4321',
            ]))
            ->assertRedirect(route('student.profile.edit'))
            ->assertSessionHas('success');

        $student->refresh();
        $this->assertSame('Maria Santos Reyes', $student->fullname);
        $this->assertSame('4th Year', $student->year_level);
        $this->assertSame('BSBA', $student->department);
        $this->assertSame('2022-4321', $student->student_id);
    }

    public function test_mobile_update_returns_to_mobile_account_editor(): void
    {
        $student = $this->student();

        $this->actingAs($student)
            ->put(route('mobile.student.profile.update'), $this->details(['birthdate' => now()->subYears(22)->toDateString()]))
            ->assertRedirect(route('mobile.student.profile.edit'));

        $this->assertSame(22, $student->fresh()->age);
    }

    public function test_age_is_worked_out_from_the_birthdate(): void
    {
        $student = $this->student();
        $birthdate = now()->subYears(19)->subMonths(3)->toDateString();

        $this->actingAs($student)
            ->put(route('student.profile.update'), $this->details(['birthdate' => $birthdate, 'age' => 99]))
            ->assertSessionHasNoErrors();

        $student->refresh();
        $this->assertSame($birthdate, $student->birthdate->toDateString());
        $this->assertSame(19, $student->age);
        $this->assertDatabaseHas('users', ['id' => $student->id, 'age' => 19]);
    }

    public function test_birthdate_must_make_the_student_at_least_17(): void
    {
        $student = $this->student();

        // One day short of turning 17.
        $this->actingAs($student)
            ->from(route('student.profile.edit'))
            ->put(route('student.profile.update'), $this->details(['birthdate' => now()->subYears(17)->addDay()->toDateString()]))
            ->assertRedirect(route('student.profile.edit'))
            ->assertSessionHasErrors(['birthdate' => 'You must be at least 17 years old.']);
        $this->assertNull($student->fresh()->birthdate);

        // Turning 17 today.
        $this->actingAs($student)
            ->put(route('student.profile.update'), $this->details(['birthdate' => now()->subYears(17)->toDateString()]))
            ->assertSessionHasNoErrors();
        $this->assertSame(17, $student->fresh()->age);
    }

    public function test_birthdate_is_required(): void
    {
        $this->actingAs($this->student())
            ->put(route('student.profile.update'), $this->details(['birthdate' => '']))
            ->assertSessionHasErrors(['birthdate' => 'Please enter your date of birth.']);
    }

    public function test_editor_shows_the_birthdate_picker_and_calculated_age(): void
    {
        $student = $this->student();
        $student->update(['birthdate' => now()->subYears(20)->toDateString()]);

        foreach (['student.profile.edit', 'mobile.student.profile.edit'] as $page) {
            $this->actingAs($student)
                ->get(route($page))
                ->assertOk()
                ->assertSee('type="date" name="birthdate"', false)
                ->assertSee('max="' . now()->subYears(17)->toDateString() . '"', false)
                ->assertSee('id="age_display" type="text"', false)
                ->assertSee('value="20" readonly', false)
                ->assertDontSee('name="age"', false);
        }
    }

    public function test_changing_email_or_password_requires_the_current_password(): void
    {
        $student = $this->student();

        $this->actingAs($student)
            ->from(route('student.profile.edit'))
            ->put(route('student.profile.update'), $this->details([
                'email' => 'updated.student@gmail.com',
                'password' => 'NewPassword456',
                'password_confirmation' => 'NewPassword456',
            ]))
            ->assertRedirect(route('student.profile.edit'))
            ->assertSessionHasErrors('current_password');

        $this->actingAs($student)
            ->put(route('student.profile.update'), $this->details([
                'email' => 'updated.student@gmail.com',
                'current_password' => 'Password123',
                'password' => 'NewPassword456',
                'password_confirmation' => 'NewPassword456',
            ]))
            ->assertSessionHasNoErrors();

        $student->refresh();
        $this->assertSame('updated.student@gmail.com', $student->email);
        $this->assertTrue(Hash::check('NewPassword456', $student->password));
    }

    private function student(): User
    {
        return User::create([
            'first_name' => 'Juan',
            'middle_name' => null,
            'last_name' => 'Dela Cruz',
            'fullname' => 'Juan Dela Cruz',
            'age' => 20,
            'year_level' => '3rd Year',
            'department' => 'BSIT',
            'student_id' => '2023-0001',
            'email' => 'juan.student@gmail.com',
            'password' => 'Password123',
            'role' => 'student',
            'status' => 'active',
        ]);
    }

    private function details(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Juan',
            'middle_name' => '',
            'last_name' => 'Dela Cruz',
            'birthdate' => now()->subYears(20)->toDateString(),
            'year_level' => '3rd Year',
            'department' => 'BSIT',
            'student_id' => '2023-0001',
            'email' => 'juan.student@gmail.com',
            'current_password' => '',
            'password' => '',
            'password_confirmation' => '',
        ], $overrides);
    }
}
