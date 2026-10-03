<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TreasurerExpenseManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $treasurer;
    private User $officer;
    private Budget $budget;
    private Expense $expense;

    protected function setUp(): void
    {
        parent::setUp();

        $this->treasurer = $this->user('treasurer', 'SSC Treasurer');
        $this->officer = $this->user('officer', 'Filing Officer');
        $this->budget = Budget::create([
            'title' => 'Activities Fund',
            'department' => 'All Departments',
            'allocated_amount' => 10000,
            'remaining_balance' => 10000,
            'school_year' => '2026-2027',
            'status' => 'Approved',
        ]);
        $this->expense = Expense::create([
            'budget_id' => $this->budget->id,
            'officer_id' => $this->officer->id,
            'expense_title' => 'Event materials',
            'amount' => 2500,
            'receipt' => 'receipts/event-materials.jpg',
            'status' => 'Pending',
        ]);
    }

    public function test_treasurer_has_the_shared_expense_management_page(): void
    {
        $this->actingAs($this->treasurer)
            ->get(route('treasurer.expenses'))
            ->assertOk()
            ->assertSee('Manage Expenses')
            ->assertSee('Event materials')
            ->assertSee('data-bs-target="#receiptModal' . $this->expense->id . '"', false)
            ->assertSee('action="' . route('treasurer.expenses.review', $this->expense) . '"', false)
            ->assertDontSee('action="' . route('admin.expenses.review', $this->expense) . '"', false);
    }

    public function test_treasurer_can_approve_an_expense_and_reduce_its_budget(): void
    {
        $this->actingAs($this->treasurer)
            ->post(route('treasurer.expenses.review', $this->expense), [
                'action' => 'approve',
                'admin_notes' => 'Receipt verified.',
            ])
            ->assertRedirect(route('treasurer.expenses'))
            ->assertSessionHas('success', 'Expense approved successfully.');

        $this->assertDatabaseHas('expenses', [
            'id' => $this->expense->id,
            'status' => 'Approved',
            'approved_by' => $this->treasurer->id,
            'admin_notes' => 'Receipt verified.',
        ]);
        $this->assertEquals(7500.0, (float) $this->budget->refresh()->remaining_balance);
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
