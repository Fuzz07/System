<?php

namespace Tests\Feature;

use App\Models\Proposal;
use App\Models\User;
use App\Notifications\AdminAlertNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ProposalResubmissionTest extends TestCase
{
    use RefreshDatabase;

    private User $officer;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->officer = $this->user('officer', 'Proposal Officer');
        $this->admin = $this->user('admin', 'Reviewing Admin');
    }

    public function test_officer_revises_and_resubmits_a_rejected_proposal(): void
    {
        $proposal = $this->rejectedProposal();

        $this->actingAs($this->officer)
            ->put(route('officer.proposals.update', $proposal), [
                'project_title' => 'Sports Fest (Revised)',
                'description' => 'Smaller venue, fewer medals.',
                'requested_budget' => '8000',
            ])
            ->assertRedirect(route('officer.proposals'))
            ->assertSessionHas('success', 'Proposal revised and resubmitted for approval.');

        $proposal->refresh();
        $this->assertSame('Pending', $proposal->status);
        $this->assertSame('Sports Fest (Revised)', $proposal->project_title);
        $this->assertEquals(8000.00, (float) $proposal->requested_budget);
        $this->assertNull($proposal->approved_by);
        $this->assertNotNull($proposal->resubmitted_at);
        // The rejection note stays for the next review.
        $this->assertSame('Budget too high for the venue.', $proposal->admin_notes);

        Notification::assertSentTo($this->admin, AdminAlertNotification::class,
            fn ($n) => $n->title === 'Proposal resubmitted for approval' && str_contains($n->message, 'Sports Fest (Revised)'));
    }

    public function test_officer_sees_the_rejection_reason_and_resubmit_option(): void
    {
        $this->rejectedProposal();

        $this->actingAs($this->officer)
            ->withViewErrors([])
            ->get(route('officer.proposals'))
            ->assertOk()
            ->assertSee('Edit &amp; Resubmit', false)
            ->assertSee('Reason: Budget too high for the venue.')
            ->assertSee('Revise &amp; Resubmit Proposal', false)
            ->assertSee('Resubmit for Approval');
    }

    public function test_admin_sees_a_resubmission_with_the_previous_note(): void
    {
        $proposal = $this->rejectedProposal();
        $this->actingAs($this->officer)->put(route('officer.proposals.update', $proposal), [
            'project_title' => 'Sports Fest', 'description' => 'Revised.', 'requested_budget' => '8000',
        ]);

        $this->actingAs($this->admin)
            ->withViewErrors([])
            ->get(route('admin.proposals'))
            ->assertOk()
            ->assertSee('Resubmitted')
            ->assertSee('Previous rejection note:')
            ->assertSee('Budget too high for the venue.');

        // A fresh review replaces the old note.
        $this->actingAs($this->admin)
            ->post(route('admin.proposals.review', $proposal), ['action' => 'approve', 'approved_budget' => '8000', 'admin_notes' => 'Looks good now.'])
            ->assertSessionHasNoErrors();
        $this->assertSame('Approved', $proposal->fresh()->status);
        $this->assertSame('Looks good now.', $proposal->fresh()->admin_notes);
    }

    public function test_resubmissions_still_respect_the_budget_limit(): void
    {
        $proposal = $this->rejectedProposal();

        $this->actingAs($this->officer)
            ->put(route('officer.proposals.update', $proposal), [
                'project_title' => 'Sports Fest', 'description' => 'Bigger.', 'requested_budget' => '150000',
            ])
            ->assertSessionHasErrors('requested_budget');

        $this->assertSame('Rejected', $proposal->fresh()->status);
    }

    public function test_approved_or_other_officers_proposals_cannot_be_edited(): void
    {
        $approved = $this->rejectedProposal(['status' => 'Approved', 'approved_budget' => 5000]);
        $someoneElses = $this->rejectedProposal(['officer_id' => $this->user('officer', 'Other Officer')->id]);
        $payload = ['project_title' => 'Changed', 'description' => 'Changed.', 'requested_budget' => '1000'];

        $this->actingAs($this->officer)->put(route('officer.proposals.update', $approved), $payload)->assertForbidden();
        $this->actingAs($this->officer)->put(route('officer.proposals.update', $someoneElses), $payload)->assertForbidden();

        $this->assertSame('Approved', $approved->fresh()->status);
        $this->assertSame('Rejected', $someoneElses->fresh()->status);
    }

    private function rejectedProposal(array $overrides = []): Proposal
    {
        return Proposal::create(array_merge([
            'officer_id' => $this->officer->id,
            'project_title' => 'Sports Fest',
            'requested_budget' => 20000,
            'description' => 'Annual sports fest.',
            'status' => 'Rejected',
            'approved_by' => $this->admin->id,
            'admin_notes' => 'Budget too high for the venue.',
        ], $overrides));
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
