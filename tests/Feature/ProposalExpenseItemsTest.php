<?php

namespace Tests\Feature;

use App\Models\Proposal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ProposalExpenseItemsTest extends TestCase
{
    use RefreshDatabase;

    private User $officer;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->officer = User::create([
            'fullname' => 'Proposal Officer',
            'email' => 'proposal-officer@example.com',
            'password' => 'password',
            'role' => 'officer',
            'status' => 'active',
        ]);
        $this->actingAs($this->officer);
    }

    public function test_expense_items_are_saved_and_requested_budget_is_their_total(): void
    {
        $response = $this->post(route('officer.proposals.store'), [
            'project_title' => 'Bulletin Board Decoration',
            'description' => 'Decorate the SSC bulletin board.',
            'requested_budget' => '999',
            'budget_items' => [
                ['description' => 'Cartolina', 'qty' => '20', 'unit_cost' => '6'],
                ['description' => 'Glue gun sticks', 'qty' => '2', 'unit_cost' => '25.50'],
                ['description' => '', 'qty' => '', 'unit_cost' => ''],
            ],
        ]);

        $response->assertRedirect(route('officer.proposals'));
        $response->assertSessionHasNoErrors();

        $proposal = Proposal::firstOrFail();
        $this->assertEquals(171.00, (float) $proposal->requested_budget);
        $this->assertSame([
            ['description' => 'Cartolina', 'qty' => 20, 'unit_cost' => 6.0, 'total' => 120.0],
            ['description' => 'Glue gun sticks', 'qty' => 2, 'unit_cost' => 25.5, 'total' => 51.0],
        ], $proposal->budgetItemList());
    }

    public function test_a_proposal_without_expense_items_still_uses_the_entered_budget(): void
    {
        $this->post(route('officer.proposals.store'), [
            'project_title' => 'Sports Fest',
            'description' => 'Annual sports fest.',
            'requested_budget' => '5000',
        ])->assertSessionHasNoErrors();

        $proposal = Proposal::firstOrFail();
        $this->assertEquals(5000.00, (float) $proposal->requested_budget);
        $this->assertNull($proposal->budget_items);
    }

    public function test_incomplete_expense_items_are_rejected(): void
    {
        $this->post(route('officer.proposals.store'), [
            'project_title' => 'Sports Fest',
            'description' => 'Annual sports fest.',
            'budget_items' => [
                ['description' => 'Medals', 'qty' => '10', 'unit_cost' => ''],
            ],
        ])->assertSessionHasErrors('budget_items.0.unit_cost');

        $this->post(route('officer.proposals.store'), [
            'project_title' => 'Sports Fest',
            'description' => 'Annual sports fest.',
            'budget_items' => [
                ['description' => 'Donated prizes', 'qty' => '3', 'unit_cost' => '0'],
            ],
        ])->assertSessionHasErrors('budget_items');

        $this->assertSame(0, Proposal::count());
    }

    public function test_pending_proposal_expense_items_can_be_edited(): void
    {
        $proposal = Proposal::create([
            'officer_id' => $this->officer->id,
            'project_title' => 'Bulletin Board Decoration',
            'requested_budget' => 120,
            'description' => 'Decorate the SSC bulletin board.',
            'budget_items' => json_encode([['description' => 'Cartolina', 'qty' => 20, 'unit_cost' => 6]]),
        ]);

        $this->put(route('officer.proposals.update', $proposal), [
            'project_title' => 'Bulletin Board Decoration',
            'description' => 'Decorate the SSC bulletin board.',
            'budget_items' => [
                ['description' => 'Cartolina', 'qty' => '20', 'unit_cost' => '6'],
                ['description' => 'Printed borders', 'qty' => '1', 'unit_cost' => '109'],
            ],
        ])->assertSessionHasNoErrors();

        $proposal->refresh();
        $this->assertEquals(229.00, (float) $proposal->requested_budget);
        $this->assertCount(2, $proposal->budgetItemList());
    }

    public function test_legacy_plain_text_budget_items_are_still_read(): void
    {
        $proposal = new Proposal(['budget_items' => "Snacks - 1,500\nTarpaulin: 350.50\nVenue"]);

        $this->assertSame([
            ['description' => 'Snacks', 'qty' => 1, 'unit_cost' => 1500.0, 'total' => 1500.0],
            ['description' => 'Tarpaulin', 'qty' => 1, 'unit_cost' => 350.5, 'total' => 350.5],
            ['description' => 'Venue', 'qty' => 1, 'unit_cost' => 0.0, 'total' => 0.0],
        ], $proposal->budgetItemList());
        $this->assertSame(1850.5, $proposal->budgetItemTotal());
    }

    public function test_expense_items_appear_on_the_officer_form_and_printed_proposal(): void
    {
        $proposal = Proposal::create([
            'officer_id' => $this->officer->id,
            'project_title' => 'Bulletin Board Decoration',
            'requested_budget' => 120,
            'description' => 'Decorate the SSC bulletin board.',
            'budget_items' => json_encode([['description' => 'Cartolina', 'qty' => 20, 'unit_cost' => 6]]),
        ]);

        $this->withViewErrors([])->get(route('officer.proposals'))
            ->assertOk()
            ->assertSee('Estimated Expenses')
            ->assertSee('name="budget_items[0][description]"', false)
            ->assertSee('value="Cartolina"', false);

        $this->get(route('proposals.print', $proposal))
            ->assertOk()
            ->assertSee('Cartolina (20 × ₱6.00)')
            ->assertSee('₱120.00');
    }

    public function test_admin_and_students_see_the_expense_breakdown(): void
    {
        $proposal = Proposal::create([
            'officer_id' => $this->officer->id,
            'project_title' => 'Bulletin Board Decoration',
            'requested_budget' => 120,
            'description' => 'Decorate the SSC bulletin board.',
            'budget_items' => json_encode([['description' => 'Cartolina', 'qty' => 20, 'unit_cost' => 6]]),
        ]);

        $admin = User::create(['fullname' => 'Admin', 'email' => 'admin@example.com', 'password' => 'password', 'role' => 'admin', 'status' => 'active']);
        $this->actingAs($admin)->withViewErrors([])->get(route('admin.proposals'))
            ->assertOk()
            ->assertSee('Total Estimated Expenses')
            ->assertSee('Cartolina');

        $student = User::create(['fullname' => 'Student', 'email' => 'student@example.com', 'password' => 'password', 'role' => 'student', 'status' => 'active']);
        $this->actingAs($student)->withViewErrors([])->get(route('student.proposal.show', $proposal))
            ->assertOk()
            ->assertSee('Estimated Expenses')
            ->assertSee('Cartolina');
    }
}
