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

        foreach (Budget::DEPARTMENTS as $department) {
            $response->assertSee('value="'.$department.'"', false);
        }
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
