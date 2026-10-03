<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TreasurerDashboardFinancialStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_approved_expenses_and_remaining_budget(): void
    {
        $treasurer = $this->user('treasurer', 'SSC Treasurer');
        $officer = $this->user('officer', 'Project Officer');
        $approvedBudget = $this->budget('Approved Fund', 10000, 'Approved');
        $this->budget('Pending Fund', 5000, 'Pending');

        $this->expense($approvedBudget, $officer, 2500, 'Approved');
        $this->expense($approvedBudget, $officer, 1000, 'Pending');

        $this->actingAs($treasurer)
            ->get(route('treasurer.dashboard'))
            ->assertOk()
            ->assertViewHas('totalBudget', fn ($value) => (float) $value === 10000.0)
            ->assertViewHas('totalExpenses', fn ($value) => (float) $value === 2500.0)
            ->assertViewHas('remainingBudget', fn ($value) => (float) $value === 7500.0)
            ->assertSee('Total Expenses')
            ->assertSee('Remaining Budget')
            ->assertSee('data-count="2500" data-currency="1"', false)
            ->assertSee('data-count="7500" data-currency="1"', false);
    }

    private function budget(string $title, float $amount, string $status): Budget
    {
        return Budget::create([
            'title' => $title,
            'department' => 'All Departments',
            'allocated_amount' => $amount,
            'remaining_balance' => $amount,
            'school_year' => '2026-2027',
            'status' => $status,
        ]);
    }

    private function expense(Budget $budget, User $officer, float $amount, string $status): Expense
    {
        return Expense::create([
            'budget_id' => $budget->id,
            'officer_id' => $officer->id,
            'expense_title' => "{$status} expense",
            'amount' => $amount,
            'status' => $status,
        ]);
    }

    private function user(string $role, string $name): User
    {
        return User::create([
            'fullname' => $name,
            'email' => str_replace(' ', '.', strtolower($name)) . '@example.com',
            'password' => 'password',
            'role' => $role,
            'status' => 'active',
        ]);
    }
}
