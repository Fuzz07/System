<?php

namespace Tests\Feature;

use App\Models\EnrollmentPayment;
use App\Models\SchoolYear;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentPaymentSemesterTest extends TestCase
{
    use RefreshDatabase;

    public function test_switching_active_school_year_hides_previous_enrollment_payment(): void
    {
        $oldSchoolYear = SchoolYear::create(['label' => '2025-2026', 'is_active' => true]);

        $student = User::create([
            'fullname' => 'Enrollment Student',
            'email' => 'enrollment.student@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'status' => 'active',
        ]);

        $oldPayment = EnrollmentPayment::create([
            'user_id' => $student->id,
            'amount' => 50,
            'semester' => $oldSchoolYear->label,
            'method' => 'walk_in',
            'status' => 'paid',
            'reference' => 'OLD-SCHOOL-YEAR-PAYMENT',
            'paid_at' => now(),
        ]);

        $oldSchoolYear->update(['is_active' => false]);
        SchoolYear::create(['label' => '2026-2027', 'is_active' => true]);

        $response = $this->actingAs($student)->get(route('student.enrollment.index'));

        $response->assertOk();
        $response->assertHeader('Cross-Origin-Opener-Policy', 'same-origin');
        $response->assertHeader('Cross-Origin-Resource-Policy', 'same-origin');
        $response->assertViewHas('payment', null);
        $this->assertDatabaseHas('enrollment_payments', [
            'id' => $oldPayment->id,
            'semester' => '2025-2026',
            'status' => 'paid',
        ]);
    }

    public function test_submitting_enrollment_payment_requires_proof_of_payment(): void
    {
        $schoolYear = SchoolYear::create(['label' => '2026-2027', 'is_active' => true]);

        $student = User::create([
            'fullname' => 'Test Student',
            'email' => 'student.proof.test@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'status' => 'active',
        ]);

        $response = $this->actingAs($student)->post(route('student.enrollment.store'), [
            'payment_method' => 'gcash',
        ]);

        $response->assertSessionHasErrors('proof');
        $this->assertDatabaseMissing('enrollment_payments', [
            'user_id' => $student->id,
            'semester' => $schoolYear->label,
        ]);
    }

    public function test_submitting_enrollment_payment_succeeds_with_proof_file(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $schoolYear = SchoolYear::create(['label' => '2026-2027', 'is_active' => true]);

        $student = User::create([
            'fullname' => 'Test Student 2',
            'email' => 'student.proof.success@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'status' => 'active',
        ]);

        $file = \Illuminate\Http\UploadedFile::fake()->image('receipt.jpg');

        $response = $this->actingAs($student)->post(route('student.enrollment.store'), [
            'payment_method' => 'gcash',
            'proof' => $file,
        ]);

        $response->assertRedirect(route('student.enrollment.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('enrollment_payments', [
            'user_id' => $student->id,
            'semester' => $schoolYear->label,
            'method' => 'gcash',
            'status' => 'pending',
            'proof_status' => 'pending',
        ]);
    }
}
