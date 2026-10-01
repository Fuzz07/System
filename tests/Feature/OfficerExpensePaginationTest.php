<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficerExpensePaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_my_expenses_shows_nine_per_page(): void
    {
        $officer = User::create([
            'fullname' => 'Expense Officer',
            'email' => 'expense.officer@example.com',
            'password' => 'password',
            'role' => 'officer',
            'status' => 'active',
        ]);
        $budget = Budget::create([
            'title' => 'Sports Fund',
            'department' => 'BSIT',
            'allocated_amount' => 10000,
            'remaining_balance' => 10000,
            'school_year' => '2026-2027',
            'status' => 'Approved',
            'created_by' => $officer->id,
        ]);

        foreach (range(1, 10) as $n) {
            Expense::create([
                'budget_id' => $budget->id,
                'officer_id' => $officer->id,
                'expense_title' => "Expense item {$n}",
                'amount' => 100,
                'status' => 'Pending',
            ]);
        }

        $firstPage = $this->actingAs($officer)->get(route('officer.expenses'))->assertOk();
        $this->assertCount(9, $firstPage->viewData('expenses')->items());
        $firstPage->assertSee('of <strong>10</strong> records', false);

        $secondPage = $this->actingAs($officer)->get(route('officer.expenses', ['page' => 2]))->assertOk();
        $this->assertCount(1, $secondPage->viewData('expenses')->items());
    }

    public function test_view_opens_the_receipt_in_a_modal(): void
    {
        $officer = User::create([
            'fullname' => 'Expense Officer',
            'email' => 'expense.officer@example.com',
            'password' => 'password',
            'role' => 'officer',
            'status' => 'active',
        ]);
        $budget = Budget::create([
            'title' => 'Sports Fund',
            'department' => 'BSIT',
            'allocated_amount' => 10000,
            'remaining_balance' => 10000,
            'school_year' => '2026-2027',
            'status' => 'Approved',
            'created_by' => $officer->id,
        ]);
        $expense = fn (string $title, ?string $receipt) => Expense::create([
            'budget_id' => $budget->id,
            'officer_id' => $officer->id,
            'expense_title' => $title,
            'amount' => 250,
            'receipt' => $receipt,
            'description' => "Details for {$title}",
            'status' => 'Pending',
        ]);
        $withImage = $expense('Jersey printing', 'receipts/jersey.jpg');
        $withPdf = $expense('Venue rental', 'receipts/venue.pdf');
        $without = $expense('Snacks', null);

        $this->actingAs($officer)
            ->get(route('officer.expenses'))
            ->assertOk()
            ->assertSee('data-bs-target="#receiptModal' . $withImage->id . '"', false)
            ->assertSee('id="receiptModal' . $withImage->id . '"', false)
            ->assertSee('<img src="' . asset('storage/receipts/jersey.jpg') . '"', false)
            ->assertSee('Details for Jersey printing')
            ->assertSee('id="receiptModal' . $withPdf->id . '"', false)
            ->assertSee("This receipt can't be previewed here.", false)
            ->assertDontSee('id="receiptModal' . $without->id . '"', false);
    }
}
