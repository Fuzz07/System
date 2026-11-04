<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\EnrollmentPayment;
use App\Models\Expense;
use App\Models\SchoolYear;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AdminDashboardChartTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::create([
            'fullname' => 'Site Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => 'admin',
            'status' => 'active',
        ]);
    }

    public function test_clicking_either_chart_opens_its_breakdown_pop_up(): void
    {
        $sports = $this->budget('Sports Fund', 'BSIT', 6000, 4500);
        $this->budget('Library Fund', 'BEED', 2000, 2000);
        $this->expense($sports, 1500, Carbon::create(2026, 9, 10));

        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            // Both the chart itself and its expand button open the pop-up.
            ->assertSee('data-bs-toggle="modal" data-bs-target="#budgetChartModal"', false)
            ->assertSee('data-bs-toggle="modal" data-bs-target="#expenseChartModal"', false)
            ->assertSee('id="budgetChartModal"', false)
            ->assertSee('id="budgetPieChartLarge"', false)
            ->assertSee('id="expenseChartModal"', false)
            ->assertSee('id="expenseBarChartLarge"', false)
            // The budget breakdown: each fund with its share of the total.
            ->assertSeeInOrder(['Sports Fund', '₱6,000.00', '75.0%', 'Library Fund', '₱2,000.00', '25.0%'])
            ->assertSee('₱8,000.00')
            ->assertSee('₱6,500.00')
            // The monthly breakdown, labelled by month name.
            ->assertSee('Sep 2026')
            ->assertSee('₱1,500.00');
    }

    public function test_the_trend_shows_the_latest_twelve_months(): void
    {
        $budget = $this->budget('General Fund', 'BSIT', 100000, 100000);
        $first = Carbon::create(2025, 8, 15);
        foreach (range(0, 12) as $offset) {
            $this->expense($budget, 100 + $offset, $first->copy()->addMonths($offset));
        }

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertOk();

        $months = $response->viewData('monthlyExpenses')->pluck('month')->all();
        $this->assertCount(12, $months);
        $this->assertSame('2025-09', $months[0]);
        $this->assertSame('2026-08', $months[11]);
        $response->assertSee('Aug 2026')->assertDontSee('Aug 2025');
    }

    public function test_pop_ups_explain_when_there_is_nothing_to_chart(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('No approved budgets yet.')
            ->assertSee('No approved expenses yet.');
    }

    public function test_contribution_chart_shows_paid_fees_grouped_by_department_for_the_active_term(): void
    {
        $term = SchoolYear::create([
            'label' => '2026-2027',
            'semester' => SchoolYear::SEMESTER_FIRST,
            'is_active' => true,
        ]);
        $firstStudent = User::create([
            'fullname' => 'First BSIT Student',
            'email' => 'first-bsit@example.com',
            'password' => 'password',
            'role' => 'student',
            'status' => 'active',
            'department' => 'BSIT',
        ]);
        $secondStudent = User::create([
            'fullname' => 'Second BSIT Student',
            'email' => 'second-bsit@example.com',
            'password' => 'password',
            'role' => 'student',
            'status' => 'active',
            'department' => 'BSIT',
        ]);
        $pendingStudent = User::create([
            'fullname' => 'Pending BEED Student',
            'email' => 'pending-beed@example.com',
            'password' => 'password',
            'role' => 'student',
            'status' => 'active',
            'department' => 'BEED',
        ]);

        foreach ([[$firstStudent, 100, 'paid'], [$secondStudent, 150, 'paid'], [$pendingStudent, 500, 'pending']] as [$student, $amount, $status]) {
            EnrollmentPayment::create([
                'user_id' => $student->id,
                'amount' => $amount,
                'semester' => $term->academic_term,
                'status' => $status,
            ]);
        }

        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Contribution Fees by Department')
            ->assertSee('Confirmed payments for 2026-2027 - First Semester')
            ->assertSee('₱250.00')
            ->assertSee('Students Paid')
            ->assertSee('id="contributionBarChart"', false)
            ->assertSee('id="contributionBarChartLarge"', false);

        $this->assertSame(250.0, (float) $response->viewData('contributionCollected'));
        $this->assertSame(2, $response->viewData('contributionPayers'));
        $this->assertCount(1, $response->viewData('contributionDepartments'));
        $this->assertSame('BSIT', $response->viewData('contributionDepartments')->first()->department);
    }

    private function budget(string $title, string $department, float $allocated, float $remaining): Budget
    {
        return Budget::create([
            'title' => $title,
            'department' => $department,
            'allocated_amount' => $allocated,
            'remaining_balance' => $remaining,
            'school_year' => '2026-2027',
            'status' => 'Approved',
        ]);
    }

    private function expense(Budget $budget, float $amount, Carbon $filedAt): void
    {
        $expense = Expense::create([
            'budget_id' => $budget->id,
            'officer_id' => $this->admin->id,
            'expense_title' => 'Expense on ' . $filedAt->format('Y-m-d'),
            'amount' => $amount,
            'status' => 'Approved',
        ]);
        $expense->forceFill(['created_at' => $filedAt])->save();
    }
}
