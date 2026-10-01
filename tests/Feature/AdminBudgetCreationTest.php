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

    public function test_funds_the_admin_adds_are_approved_without_a_separate_step(): void
    {
        $this->post(route('admin.budgets.store'), [
            'title' => 'Sports Fund',
            'department' => 'BSIT',
            'allocated_amount' => '5000',
            'school_year' => '2026-2027',
        ])->assertRedirect(route('admin.budgets'));

        $budget = Budget::where('title', 'Sports Fund')->firstOrFail();
        $this->assertSame('Approved', $budget->status);
        $this->assertSame(auth()->id(), (int) $budget->approved_by);

        $this->withViewErrors([])->get(route('admin.budgets'))
            ->assertOk()
            ->assertDontSee('action="' . route('admin.budgets.approve', $budget) . '"', false)
            ->assertDontSee('action="' . route('admin.budgets.reject', $budget) . '"', false);
    }

    public function test_budget_table_shows_ten_per_page_while_the_report_keeps_all(): void
    {
        foreach (range(1, 12) as $n) {
            Budget::create([
                'title' => sprintf('Fund %02d', $n),
                'department' => 'BSIT',
                'allocated_amount' => 1000,
                'remaining_balance' => 1000,
                'school_year' => '2026-2027',
                'status' => 'Approved',
                'created_by' => auth()->id(),
            ]);
        }

        $firstPage = $this->withViewErrors([])->get(route('admin.budgets', ['sort' => 'title_asc']))->assertOk();
        $this->assertCount(10, $firstPage->viewData('budgetPage')->items());
        $this->assertCount(12, $firstPage->viewData('budgets'));
        $firstPage->assertSee('of <strong>12</strong> records', false);

        $secondPage = $this->withViewErrors([])->get(route('admin.budgets', ['sort' => 'title_asc', 'page' => 2]))->assertOk();
        $this->assertSame(['Fund 11', 'Fund 12'], collect($secondPage->viewData('budgetPage')->items())->pluck('title')->all());
        $this->assertStringContainsString('sort=title_asc', $secondPage->viewData('budgetPage')->withQueryString()->url(1));
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

    /**
     * @dataProvider strictlyRejectedInput
     */
    public function test_add_funds_rejects_input_outside_the_strict_rules(string $field, array $overrides): void
    {
        $this->from(route('admin.budgets'))
            ->post(route('admin.budgets.store'), $overrides + $this->validFunds())
            ->assertRedirect(route('admin.budgets'))
            ->assertSessionHasErrors($field);

        $this->assertDatabaseCount('budgets', 0);
    }

    public static function strictlyRejectedInput(): array
    {
        return [
            'title too short' => ['title', ['title' => 'AB']],
            'title too long' => ['title', ['title' => str_repeat('A', Budget::TITLE_MAX + 1)]],
            'title with symbols' => ['title', ['title' => 'Fund <script>']],
            'title starting with punctuation' => ['title', ['title' => '-Sports Fund']],
            'title of only digits' => ['title', ['title' => '2026']],
            'reserved contribution fee title' => ['title', ['title' => 'contribution fees extra']],
            'amount over the limit' => ['allocated_amount', ['allocated_amount' => (string) (Budget::MAX_ALLOCATION + 1)]],
            'amount a centavo over the limit' => ['allocated_amount', ['allocated_amount' => Budget::MAX_ALLOCATION . '.01']],
            'amount far over the limit' => ['allocated_amount', ['allocated_amount' => '10000000']],
            'amount with commas' => ['allocated_amount', ['allocated_amount' => '15,000']],
            'amount with three decimals' => ['allocated_amount', ['allocated_amount' => '15000.505']],
            'amount below one' => ['allocated_amount', ['allocated_amount' => '0.50']],
            'school year not offered' => ['school_year', ['school_year' => '2090-2091']],
            'notes too long' => ['notes', ['notes' => str_repeat('a', Budget::NOTES_MAX + 1)]],
        ];
    }

    public function test_add_funds_accepts_the_largest_allowed_amount_and_tidies_the_title(): void
    {
        $this->from(route('admin.budgets'))
            ->post(route('admin.budgets.store'), [
                'title' => "  Sports   Fund (Phase 2)  ",
                'allocated_amount' => (string) Budget::MAX_ALLOCATION,
            ] + $this->validFunds())
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('budgets', [
            'title' => 'Sports Fund (Phase 2)',
            'allocated_amount' => 100000,
        ]);
    }

    public function test_amount_field_carries_the_limit_for_the_live_check(): void
    {
        $this->withViewErrors([])->get(route('admin.budgets'))
            ->assertOk()
            ->assertSee('data-max="100000"', false)
            ->assertSee('data-max-label="₱100,000.00"', false)
            ->assertSee('id="allocatedAmountLimit"', false)
            ->assertSee('at most ₱100,000.00');
    }

    public function test_a_fund_title_cannot_repeat_within_a_department_and_school_year(): void
    {
        $this->post(route('admin.budgets.store'), $this->validFunds())->assertSessionHasNoErrors();

        $this->from(route('admin.budgets'))
            ->post(route('admin.budgets.store'), ['title' => 'Technology  Fund'] + $this->validFunds())
            ->assertSessionHasErrors(['title' => 'A fund with this title already exists for this department and school year.']);

        // The same title is fine for another department. (The rejected attempt's
        // errors are cleared first, or they would linger into this request.)
        $this->flushSession();
        $this->post(route('admin.budgets.store'), ['department' => 'BSED'] + $this->validFunds())
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('budgets', 2);
    }

    private function validFunds(): array
    {
        return [
            'title' => 'Technology Fund',
            'department' => 'BSIT',
            'allocated_amount' => '15000',
            'school_year' => now()->year . '-' . (now()->year + 1),
        ];
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
