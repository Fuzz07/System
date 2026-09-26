<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\EnrollmentPayment;
use App\Models\SchoolYear;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EnrollmentAddStudentTest extends TestCase
{
    use RefreshDatabase;

    private SchoolYear $schoolYear;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->schoolYear = SchoolYear::create(['label' => '2026-2027', 'is_active' => true]);
    }

    public function test_admin_can_add_an_unpaid_student(): void
    {
        $this->actingAs($this->user('admin'));

        $response = $this->post(route('admin.enrollment.payments.students.store'), $this->studentForm([
            'payment_status' => 'unpaid',
        ]));

        $response->assertRedirect(route('admin.enrollment.payments', ['search' => '2026-0042']));
        $response->assertSessionHasNoErrors();

        $student = User::where('email', 'juan.delacruz@mcclawis.edu.ph')->firstOrFail();
        $this->assertSame('Juan Santos Dela Cruz', $student->fullname);
        $this->assertSame('student', $student->role);
        $this->assertSame('active', $student->status);
        $this->assertSame('BSIT', $student->department);
        $this->assertSame('2nd Year', $student->year_level);
        $this->assertSame(0, EnrollmentPayment::count());
    }

    public function test_adding_a_paid_student_records_the_payment_and_credits_the_budget(): void
    {
        $this->actingAs($this->user('admin'));

        $this->post(route('admin.enrollment.payments.students.store'), $this->studentForm([
            'payment_status' => 'paid',
            'reference' => 'OR-7384',
        ]))->assertSessionHasNoErrors();

        $student = User::where('student_id', '2026-0042')->firstOrFail();
        $payment = EnrollmentPayment::where('user_id', $student->id)->firstOrFail();
        $this->assertSame('paid', $payment->status);
        $this->assertSame('walk_in', $payment->method);
        $this->assertSame('OR-7384', $payment->reference);
        $this->assertSame($this->schoolYear->academic_term, $payment->semester);
        $this->assertEquals(config('ssc.enrollment_fee_amount', 50), (float) $payment->amount);

        $budget = Budget::where('title', Budget::ENROLLMENT_TITLE_PREFIX)->firstOrFail();
        $this->assertEquals(config('ssc.enrollment_fee_amount', 50), (float) $budget->allocated_amount);
    }

    public function test_duplicate_or_invalid_students_are_rejected(): void
    {
        $this->actingAs($this->user('admin'));
        User::create([
            'fullname' => 'Existing Student',
            'email' => 'juan.delacruz@mcclawis.edu.ph',
            'student_id' => '2026-0042',
            'password' => 'password',
            'role' => 'student',
            'status' => 'active',
        ]);

        $this->post(route('admin.enrollment.payments.students.store'), $this->studentForm())
            ->assertSessionHasErrors(['email', 'student_id']);

        $this->post(route('admin.enrollment.payments.students.store'), $this->studentForm([
            'email' => 'someone@gmail.com',
            'student_id' => '42',
            'department' => 'BSCS',
        ]))->assertSessionHasErrors(['email', 'student_id', 'department']);

        $this->assertSame(1, User::where('role', 'student')->count());
    }

    public function test_treasurer_has_the_enrollment_payments_page(): void
    {
        $this->actingAs($this->user('treasurer'));

        $this->withViewErrors([])->get(route('treasurer.enrollment.payments'))
            ->assertOk()
            ->assertSee('Semester Enrollment Payments')
            ->assertSee(route('treasurer.enrollment.payments'), false)
            ->assertSee(route('treasurer.cashbook'), false)
            ->assertSee('action="' . route('treasurer.enrollment.payments.students.store') . '"', false)
            ->assertDontSee('Open Budgets');
    }

    public function test_treasurer_can_add_a_student_and_mark_an_unpaid_student_paid(): void
    {
        $this->actingAs($this->user('treasurer'));

        $this->post(route('treasurer.enrollment.payments.students.store'), $this->studentForm())
            ->assertRedirect(route('treasurer.enrollment.payments', ['search' => '2026-0042']));

        $student = User::where('student_id', '2026-0042')->firstOrFail();
        $this->assertSame(0, EnrollmentPayment::count());

        $this->from(route('treasurer.enrollment.payments'))
            ->post(route('treasurer.enrollment.payments.walk_in', $student))
            ->assertRedirect(route('treasurer.enrollment.payments'));

        $this->assertSame('paid', EnrollmentPayment::where('user_id', $student->id)->value('status'));
    }

    public function test_admin_page_keeps_the_admin_navigation(): void
    {
        $this->actingAs($this->user('admin'));

        $this->withViewErrors([])->get(route('admin.enrollment.payments'))
            ->assertOk()
            ->assertSee('Add Student')
            ->assertSee('Open Budgets')
            ->assertSee('action="' . route('admin.enrollment.payments.students.store') . '"', false);
    }

    public function test_other_roles_cannot_manage_enrollment_payments(): void
    {
        foreach (['officer', 'student'] as $role) {
            $user = $this->user($role);
            $this->actingAs($user)->get(route('treasurer.enrollment.payments'))->assertForbidden();
            $this->actingAs($user)->post(route('treasurer.enrollment.payments.students.store'), $this->studentForm())->assertForbidden();
        }

        $this->assertNull(User::where('student_id', '2026-0042')->first());
    }

    private function user(string $role): User
    {
        return User::create([
            'fullname' => ucfirst($role) . ' User',
            'email' => "{$role}@example.com",
            'password' => 'password',
            'role' => $role,
            'status' => 'active',
        ]);
    }

    private function studentForm(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Juan',
            'middle_name' => 'Santos',
            'last_name' => 'Dela Cruz',
            'student_id' => '2026-0042',
            'email' => 'Juan.DelaCruz@mcclawis.edu.ph',
            'department' => 'BSIT',
            'year_level' => '2nd Year',
            'payment_status' => 'unpaid',
        ], $overrides);
    }
}
