<?php

namespace Tests\Feature;

use App\Models\Budget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WelcomeBudgetManagedTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_budget_managed_uses_only_approved_allocations(): void
    {
        $this->budget('Approved fund', 225950.75, 'Approved');
        $this->budget('Pending fund', 99000, 'Pending');
        $this->budget('Rejected fund', 50000, 'Rejected');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-managed-budget="225950.75"', false)
            ->assertSee('₱225.9<span>k+</span>', false)
            ->assertDontSee('₱340<span>k+</span>', false);
    }

    public function test_landing_page_budget_managed_scales_to_millions(): void
    {
        $this->budget('Large approved fund', 1250000, 'Approved');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('₱1.2<span>m+</span>', false);
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
}
