<?php

namespace Tests\Feature;

use App\Models\Proposal;
use App\Models\ProposalComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OfficerProposalDeleteTest extends TestCase
{
    use RefreshDatabase;

    private User $officer;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->officer = $this->user('officer', 'Proposal Officer');
    }

    public function test_officer_can_delete_their_pending_or_rejected_proposal(): void
    {
        $student = $this->user('student', 'Student Voice');

        foreach (['Pending', 'Rejected'] as $status) {
            $proposal = $this->proposal($this->officer, $status);
            $comment = ProposalComment::create(['proposal_id' => $proposal->id, 'user_id' => $student->id, 'comment' => 'Feedback']);
            ProposalComment::create(['proposal_id' => $proposal->id, 'user_id' => $student->id, 'comment' => 'Reply', 'parent_id' => $comment->id]);

            $this->actingAs($this->officer)
                ->delete(route('officer.proposals.destroy', $proposal))
                ->assertRedirect(route('officer.proposals'))
                ->assertSessionHas('success');

            $this->assertDatabaseMissing('proposals', ['id' => $proposal->id]);
            $this->assertDatabaseMissing('proposal_comments', ['proposal_id' => $proposal->id]);
        }
    }

    public function test_approved_proposals_cannot_be_deleted(): void
    {
        $proposal = $this->proposal($this->officer, 'Approved');

        $this->actingAs($this->officer)
            ->delete(route('officer.proposals.destroy', $proposal))
            ->assertForbidden();

        $this->assertDatabaseHas('proposals', ['id' => $proposal->id]);
    }

    public function test_officer_cannot_delete_another_officers_proposal(): void
    {
        $proposal = $this->proposal($this->user('officer', 'Other Officer'), 'Pending');

        $this->actingAs($this->officer)
            ->delete(route('officer.proposals.destroy', $proposal))
            ->assertForbidden();

        $this->assertDatabaseHas('proposals', ['id' => $proposal->id]);
    }

    public function test_delete_button_shows_only_on_proposals_that_can_be_deleted(): void
    {
        $pending = $this->proposal($this->officer, 'Pending');
        $approved = $this->proposal($this->officer, 'Approved');

        $this->actingAs($this->officer)
            ->get(route('officer.proposals'))
            ->assertOk()
            // Whole attributes, since other proposal URLs start with the delete URL.
            ->assertSee('action="' . route('officer.proposals.destroy', $pending) . '"', false)
            ->assertDontSee('action="' . route('officer.proposals.destroy', $approved) . '"', false);
    }

    private function proposal(User $officer, string $status): Proposal
    {
        return Proposal::create([
            'officer_id' => $officer->id,
            'project_title' => "{$status} project",
            'description' => 'Project description.',
            'requested_budget' => 1000,
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
