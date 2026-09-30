<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBudgetCreationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->actingAs(User::create([
            'fullname' => 'Budget Administrator',
            'email' => 'budget-admin@example.com',
            'password' => 'password',
            'role' => 'admin',
            'status' => 'active',
        ]));
    }

    public function test_budget_form_contains_the_five_department_and_school_year_options(): void
    {
        $response = $this->withViewErrors([])->get(route('admin.budgets'));

        $response->assertOk();
        $response->assertViewHas('departmentOptions', Budget::DEPARTMENTS);
        $response->assertSee('<select id="budgetDepartment"', false);
        $response->assertSee('<select id="budgetSchoolYear"', false);
        $response->assertSee('Budget Management Report');
        $response->assertSee('Department Summary');
        $response->assertSee('Detailed Budget Breakdown');

        foreach (Budget::DEPARTMENTS as $department) {
            $response->assertSee('value="'.$department.'"', false);
        }
    }

    public function test_funds_can_be_added_for_all_departments(): void
    {
        $this->withViewErrors([])->get(route('admin.budgets'))
            ->assertOk()
            ->assertSee('value="All Departments"', false);

        $this->post(route('admin.budgets.store'), [
            'title' => 'Council General Fund',
            'department' => 'All Departments',
            'allocated_amount' => '20000',
            'school_year' => '2026-2027',
        ])->assertRedirect(route('admin.budgets'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Funds added successfully.');

        $this->assertDatabaseHas('budgets', [
            'title' => 'Council General Fund',
            'department' => 'All Departments',
            'allocated_amount' => 20000,
        ]);
        $this->assertDatabaseCount('budgets', 1);
    }

    public function test_budget_page_uses_contribution_fee_wording(): void
    {
        $this->withViewErrors([])->get(route('admin.budgets'))
            ->assertOk()
            ->assertSee('Contribution Fees Fund')
            ->assertSee('Contribution Fees Only')
            ->assertSee('Contribution Fees', false)
            ->assertDontSee('Enrollment Payments')
            ->assertDontSee('Enrollment Fees');
    }

    public function test_existing_enrollment_fee_budgets_are_renamed(): void
    {
        foreach (['Enrollment Fees', 'Enrollment Fees - BSIT', 'Enrollment Fund Drive'] as $title) {
            Budget::create([
                'title' => $title,
                'department' => 'All Departments',
                'allocated_amount' => 100,
                'remaining_balance' => 100,
                'school_year' => '2026-2027',
                'status' => 'Approved',
                'created_by' => auth()->id(),
                'notes' => 'Consolidated enrollment fees collection for all departments.',
            ]);
        }

        $migration = require database_path('migrations/2026_09_30_000003_rename_enrollment_fee_budgets_to_contribution_fees.php');
        $migration->up();

        $this->assertSame(
            ['Contribution Fees', 'Contribution Fees - BSIT', 'Enrollment Fund Drive'],
            Budget::orderBy('id')->pluck('title')->all()
        );
        $this->assertSame(2, Budget::enrollmentFees()->count());
        $this->assertSame(3, Budget::where('notes', 'Consolidated contribution fees collection for all departments.')->count());
    }

    public function test_print_report_contains_a_normalized_financial_breakdown(): void
    {
        Budget::create([
            'title' => 'Laboratory Equipment',
            'department' => 'BSIT',
            'allocated_amount' => 1000,
            'remaining_balance' => 750,
            'school_year' => '2026-2027',
            'status' => 'Approved',
            'created_by' => auth()->id(),
            'notes' => 'Computer laboratory supplies.',
        ]);

        $response = $this->withViewErrors([])->get(route('admin.budgets'));

        $response->assertOk();
        $response->assertSee('Approved Allocation');
        $response->assertSee('Funds Used');
        $response->assertSee('Remaining Balance');
        $response->assertSee('₱1,000.00');
        $response->assertSee('₱250.00');
        $response->assertSee('₱750.00');
        $response->assertSee('Computer laboratory supplies.');
    }

    public function test_a_budget_can_be_created_with_valid_dropdown_values(): void
    {
        $response = $this->post(route('admin.budgets.store'), [
            'title' => 'Technology Fund',
            'department' => 'BSIT',
            'allocated_amount' => '15000.50',
            'school_year' => '2026-2027',
            'notes' => 'For laboratory supplies.',
        ]);

        $response->assertRedirect(route('admin.budgets'));
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('budgets', [
            'title' => 'Technology Fund',
            'department' => 'BSIT',
            'allocated_amount' => 15000.50,
            'remaining_balance' => 15000.50,
            'school_year' => '2026-2027',
        ]);
    }

    public function test_an_allocated_amount_with_a_leading_zero_is_rejected(): void
    {
        $response = $this->from(route('admin.budgets'))->post(route('admin.budgets.store'), [
            'title' => 'Technology Fund',
            'department' => 'BSIT',
            'allocated_amount' => '015000.50',
            'school_year' => '2026-2027',
        ]);

        $response->assertRedirect(route('admin.budgets'));
        $response->assertSessionHasErrors('allocated_amount');
        $this->assertDatabaseCount('budgets', 0);
    }

    public function test_a_department_outside_the_five_options_is_rejected(): void
    {
        $response = $this->from(route('admin.budgets'))->post(route('admin.budgets.store'), [
            'title' => 'Technology Fund',
            'department' => 'OTHER',
            'allocated_amount' => '15000',
            'school_year' => '2026-2027',
        ]);

        $response->assertRedirect(route('admin.budgets'));
        $response->assertSessionHasErrors('department');
        $this->assertDatabaseCount('budgets', 0);
    }

    public function test_school_years_must_be_consecutive(): void
    {
        $response = $this->from(route('admin.budgets'))->post(route('admin.budgets.store'), [
            'title' => 'Technology Fund',
            'department' => 'BSIT',
            'allocated_amount' => '15000',
            'school_year' => '2026-2028',
        ]);

        $response->assertRedirect(route('admin.budgets'));
        $response->assertSessionHasErrors('school_year');
        $this->assertDatabaseCount('budgets', 0);
    }
}
