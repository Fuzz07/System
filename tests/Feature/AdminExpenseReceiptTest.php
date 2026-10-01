<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminExpenseReceiptTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_views_receipts_in_the_same_modal_as_officers(): void
    {
        $admin = $this->user('admin', 'Site Admin');
        $officer = $this->user('officer', 'Filing Officer');
        $budget = Budget::create([
            'title' => 'Sports Fund',
            'department' => 'BSIT',
            'allocated_amount' => 10000,
            'remaining_balance' => 10000,
            'school_year' => '2026-2027',
            'status' => 'Approved',
        ]);
        $pending = $this->expense($budget, $officer, 'Jersey printing', 'Pending');
        $approved = $this->expense($budget, $officer, 'Venue rental', 'Approved', $admin);

        $html = $this->actingAs($admin)
            ->get(route('admin.expenses'))
            ->assertOk()
            ->assertSee('data-bs-target="#receiptModal' . $pending->id . '"', false)
            ->assertSee('id="receiptModal' . $approved->id . '"', false)
            ->assertSee('<img src="' . asset('storage/receipts/jersey-printing.jpg') . '"', false)
            ->assertSee('Filed By')
            ->assertSee('Filing Officer')
            ->assertSee('Reviewed By')
            ->getContent();

        // Only the pending expense's receipt offers a way into its review form.
        $this->assertStringContainsString('data-bs-target="#reviewExpense' . $pending->id . '"><i class="bi bi-pencil-square"></i> Review', $html);
        $this->assertStringNotContainsString('#reviewExpense' . $approved->id, $html);
        $this->assertStringNotContainsString('target="_blank" class="btn btn-outline-primary btn-sm" style="font-size:.72rem;"><i class="bi bi-file-earmark"></i> View', $html);
    }

    private function expense(Budget $budget, User $officer, string $title, string $status, ?User $approver = null): Expense
    {
        return Expense::create([
            'budget_id' => $budget->id,
            'officer_id' => $officer->id,
            'expense_title' => $title,
            'amount' => 500,
            'receipt' => 'receipts/' . str_replace(' ', '-', strtolower($title)) . '.jpg',
            'status' => $status,
            'approved_by' => $approver?->id,
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
