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
        $response->assertHeader('Cross-Origin-Embedder-Policy', 'require-corp');
        $response->assertViewHas('payment', null);
        $this->assertDatabaseHas('enrollment_payments', [
            'id' => $oldPayment->id,
            'semester' => '2025-2026',
            'status' => 'paid',
        ]);
    }
}
