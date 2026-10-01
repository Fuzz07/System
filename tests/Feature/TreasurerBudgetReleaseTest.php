<?php

namespace Tests\Feature;

use App\Http\Controllers\Treasurer\ReleaseController;
use App\Models\BudgetRelease;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TreasurerBudgetReleaseTest extends TestCase
{
    use RefreshDatabase;

    private User $treasurer;
    private Proposal $proposal;

    protected function setUp(): void
    {
        parent::setUp();
        $this->treasurer = $this->user('treasurer', 'Florane Maru');
        $officer = $this->user('officer', 'Project Officer');
        $this->proposal = Proposal::create([
            'officer_id' => $officer->id,
            'project_title' => 'Sports Fest',
            'requested_budget' => 10000,
            'approved_budget' => 10000,
            'description' => 'Annual sports fest.',
            'status' => 'Approved',
        ]);
        $this->actingAs($this->treasurer);
    }

    public function test_treasurer_releases_a_budget_with_a_reference_number(): void
    {
        $this->post(route('treasurer.release.submit'), $this->payload())
            ->assertRedirect(route('treasurer.release'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('budget_releases', [
            'proposal_id' => $this->proposal->id,
            'reference_no' => 'GC-1234567890',
            'release_status' => 'Released',
            'released_by' => $this->treasurer->id,
        ]);
    }

    public function test_a_release_needs_a_reference_number(): void
    {
        $this->post(route('treasurer.release.submit'), $this->payload(['reference_no' => '']))
            ->assertSessionHasErrors('reference_no');
        $this->post(route('treasurer.release.submit'), $this->payload(['reference_no' => '   ']))
            ->assertSessionHasErrors('reference_no');

        $this->assertSame(0, BudgetRelease::count());
    }

    public function test_every_method_but_cash_needs_a_reference_number(): void
    {
        foreach (array_diff(ReleaseController::RELEASE_METHODS, [ReleaseController::CASH]) as $method) {
            $this->post(route('treasurer.release.submit'), $this->payload(['release_method' => $method, 'reference_no' => '']))
                ->assertSessionHasErrors('reference_no');
        }

        $this->assertSame(0, BudgetRelease::count());
    }

    public function test_a_cash_release_has_no_reference_number(): void
    {
        $this->post(route('treasurer.release.submit'), $this->payload([
            'release_method' => 'Cash', 'reference_no' => '', 'amount_released' => '4000', 'release_status' => 'Partial',
        ]))->assertSessionHasNoErrors();
        // One sent anyway (from an outdated page, say) is not recorded, and a
        // second cash release does not clash with the first.
        $this->post(route('treasurer.release.submit'), $this->payload([
            'release_method' => 'Cash', 'reference_no' => 'Not Applicable', 'amount_released' => '6000',
        ]))->assertSessionHasNoErrors();

        $this->assertSame(2, BudgetRelease::where('release_method', 'Cash')->whereNull('reference_no')->count());
    }

    public function test_the_form_shows_the_reference_as_not_applicable_only_for_cash(): void
    {
        // Cash is the first method, so a fresh form starts on it.
        $this->get(route('treasurer.release', ['proposal_id' => $this->proposal->id]))
            ->assertOk()
            ->assertSee('value="Not Applicable" disabled', false);

        // Reopened after a failed GCash release, the field is required and keeps what was typed.
        $this->from(route('treasurer.release'))
            ->post(route('treasurer.release.submit'), $this->payload(['reference_no' => 'GC1']))
            ->assertSessionHasErrors('reference_no');
        $this->get(route('treasurer.release'))
            ->assertOk()
            ->assertSee('value="GC1" required', false)
            ->assertDontSee('value="Not Applicable"', false);
    }

    public function test_reference_numbers_must_be_well_formed_and_unique(): void
    {
        foreach (['12', '#ref<script>', '-1234', str_repeat('9', 51)] as $bad) {
            $this->post(route('treasurer.release.submit'), $this->payload(['reference_no' => $bad]))
                ->assertSessionHasErrors('reference_no');
        }

        $this->post(route('treasurer.release.submit'), $this->payload(['amount_released' => '4000', 'release_status' => 'Partial']))
            ->assertSessionHasNoErrors();
        $this->post(route('treasurer.release.submit'), $this->payload(['amount_released' => '6000']))
            ->assertSessionHasErrors('reference_no');

        $this->assertSame(1, BudgetRelease::count());
    }

    public function test_the_amount_cannot_pass_the_remaining_balance(): void
    {
        $this->post(route('treasurer.release.submit'), $this->payload(['amount_released' => '10000.01']))
            ->assertSessionHasErrors('amount_released');
        $this->post(route('treasurer.release.submit'), $this->payload(['amount_released' => '100.555', 'release_status' => 'Partial']))
            ->assertSessionHasErrors('amount_released');

        $this->post(route('treasurer.release.submit'), $this->payload())->assertSessionHasNoErrors();
        // Fully released: not even one more centavo.
        $this->post(route('treasurer.release.submit'), $this->payload(['amount_released' => '0.01', 'reference_no' => 'GC-2', 'release_status' => 'Partial']))
            ->assertSessionHasErrors('amount_released');

        $this->assertSame(1, BudgetRelease::count());
    }

    public function test_the_status_must_match_the_amount(): void
    {
        $this->post(route('treasurer.release.submit'), $this->payload(['amount_released' => '4000', 'release_status' => 'Released']))
            ->assertSessionHasErrors('release_status');
        $this->post(route('treasurer.release.submit'), $this->payload(['amount_released' => '10000', 'release_status' => 'Partial']))
            ->assertSessionHasErrors('release_status');

        $this->assertSame(0, BudgetRelease::count());
    }

    public function test_only_listed_methods_and_approved_proposals_are_accepted(): void
    {
        $this->post(route('treasurer.release.submit'), $this->payload(['release_method' => 'Crypto']))
            ->assertSessionHasErrors('release_method');

        $this->proposal->update(['status' => 'Pending']);
        $this->post(route('treasurer.release.submit'), $this->payload())
            ->assertSessionHasErrors('proposal_id');

        $this->assertSame(0, BudgetRelease::count());
    }

    public function test_a_failed_release_reopens_the_form_with_what_was_typed(): void
    {
        $this->from(route('treasurer.release'))
            ->post(route('treasurer.release.submit'), $this->payload(['reference_no' => '', 'notes' => 'Handed to the officer.']))
            ->assertRedirect(route('treasurer.release'));

        $this->get(route('treasurer.release'))
            ->assertOk()
            ->assertSee('value="' . $this->proposal->id . '"', false)
            ->assertSee('Handed to the officer.')
            ->assertSee('Enter the reference / transaction number for this release');
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'proposal_id' => $this->proposal->id,
            'amount_released' => '10000',
            'release_method' => 'GCash',
            'reference_no' => 'GC-1234567890',
            'release_status' => 'Released',
            'notes' => '',
        ], $overrides);
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
