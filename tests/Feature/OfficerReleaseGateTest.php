<?php

namespace Tests\Feature;

use App\Models\BudgetRelease;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OfficerReleaseGateTest extends TestCase
{
    use RefreshDatabase;

    private User $officer;
    private User $treasurer;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        Storage::fake('public');
        $this->officer = $this->user('officer', 'Project Officer');
        $this->treasurer = $this->user('treasurer', 'Council Treasurer');
    }

    public function test_approved_project_waits_for_the_budget_release_before_completing(): void
    {
        $proposal = $this->approvedProposal();

        $this->actingAs($this->officer)
            ->get(route('officer.proposals'))
            ->assertOk()
            ->assertSee('Awaiting Release')
            ->assertDontSee('id="completeModal' . $proposal->id . '"', false);

        $this->actingAs($this->officer)
            ->post(route('officer.proposals.complete', $proposal), ['receipt' => UploadedFile::fake()->image('receipt.jpg')])
            ->assertRedirect(route('officer.proposals'))
            ->assertSessionHas('danger');

        $this->assertSame('Ongoing', $proposal->fresh()->project_status);
    }

    public function test_a_partial_release_is_not_enough(): void
    {
        $proposal = $this->approvedProposal();
        $this->release($proposal, 4000, 'Partial');

        $this->actingAs($this->officer)
            ->get(route('officer.proposals'))
            ->assertSee('Awaiting Release')
            ->assertSee('4,000.00');

        $this->actingAs($this->officer)
            ->post(route('officer.proposals.complete', $proposal), ['receipt' => UploadedFile::fake()->image('receipt.jpg')])
            ->assertSessionHas('danger');

        $this->assertSame('Ongoing', $proposal->fresh()->project_status);
    }

    public function test_project_can_be_completed_once_the_full_budget_is_released(): void
    {
        $proposal = $this->approvedProposal();
        $this->release($proposal, 4000, 'Partial');
        $this->release($proposal, 6000, 'Released');

        $this->actingAs($this->officer)
            ->get(route('officer.proposals'))
            ->assertDontSee('Awaiting Release')
            ->assertSee('id="completeModal' . $proposal->id . '"', false);

        $this->actingAs($this->officer)
            ->post(route('officer.proposals.complete', $proposal), ['receipt' => UploadedFile::fake()->image('receipt.jpg')])
            ->assertRedirect(route('officer.proposals'))
            ->assertSessionHas('success');

        $this->assertSame('Completed', $proposal->fresh()->project_status);
    }

    public function test_liquidation_waits_for_the_full_budget_release(): void
    {
        $waiting = $this->approvedProposal('Sports Fest');
        $released = $this->approvedProposal('Clean-up Drive');
        $this->release($released, 10000, 'Released');

        $page = $this->actingAs($this->officer)->get(route('officer.liquidation'))->assertOk();
        $this->assertSame([$released->id], $page->viewData('proposals')->pluck('id')->values()->all());
        $page->assertSee("Waiting for the treasurer's budget release", false)
            ->assertSee('Sports Fest');

        $this->actingAs($this->officer)
            ->from(route('officer.liquidation'))
            ->post(route('officer.liquidation.store'), $this->liquidation($waiting))
            ->assertSessionHasErrors('proposal_id');
        $this->assertDatabaseMissing('liquidations', ['proposal_id' => $waiting->id]);

        $this->flushSession();
        $this->actingAs($this->officer)
            ->post(route('officer.liquidation.store'), $this->liquidation($released))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('liquidations', ['proposal_id' => $released->id]);
    }

    private function approvedProposal(string $title = 'Campus Project'): Proposal
    {
        return Proposal::create([
            'officer_id' => $this->officer->id,
            'project_title' => $title,
            'description' => 'Project description.',
            'requested_budget' => 10000,
            'approved_budget' => 10000,
            'status' => 'Approved',
        ]);
    }

    private function release(Proposal $proposal, float $amount, string $status): void
    {
        BudgetRelease::create([
            'proposal_id' => $proposal->id,
            'released_by' => $this->treasurer->id,
            'amount_released' => $amount,
            'release_method' => 'Cash',
            'reference_no' => 'REF-' . uniqid(),
            'release_status' => $status,
        ]);
    }

    private function liquidation(Proposal $proposal): array
    {
        return [
            'title' => 'Liquidation for ' . $proposal->project_title,
            'proposal_id' => $proposal->id,
            'liq_file' => UploadedFile::fake()->image('liquidation.jpg'),
        ];
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
