<?php

namespace Tests\Feature;

use App\Models\Proposal;
use App\Models\ProposalComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OfficerProposalFeedbackTest extends TestCase
{
    use RefreshDatabase;

    private User $officer;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->officer = $this->user('officer', 'Proposal Officer');
    }

    public function test_officer_can_read_student_feedback_on_their_proposals(): void
    {
        $proposal = $this->proposal($this->officer, 'Campus Clean-up Drive');
        $student = $this->user('student', 'Maria Santos');
        $this->comment($proposal, $student, 'Please add more trash bins near the canteen.');
        $this->comment($proposal, $this->officer, 'Noted, we will include them in the budget.');

        $this->actingAs($this->officer)
            ->get(route('officer.proposals'))
            ->assertOk()
            ->assertSee('Feedback (2)')
            ->assertSee('Please add more trash bins near the canteen.')
            ->assertSee('Noted, we will include them in the budget.');
    }

    public function test_student_names_stay_anonymous_to_officers(): void
    {
        $proposal = $this->proposal($this->officer, 'Campus Clean-up Drive');
        $this->comment($proposal, $this->user('student', 'Maria Santos'), 'Schedule it on a Saturday.');

        $this->actingAs($this->officer)
            ->get(route('officer.proposals'))
            ->assertOk()
            ->assertSee('Anonymous Student')
            ->assertDontSee('Maria Santos');
    }

    public function test_officer_does_not_see_feedback_on_other_officers_proposals(): void
    {
        $otherProposal = $this->proposal($this->user('officer', 'Other Officer'), 'Sports Fest');
        $this->comment($otherProposal, $this->user('student', 'Juan Cruz'), 'Add a chess tournament.');

        $this->actingAs($this->officer)
            ->get(route('officer.proposals'))
            ->assertOk()
            ->assertDontSee('Add a chess tournament.');
    }

    private function proposal(User $officer, string $title): Proposal
    {
        return Proposal::create([
            'officer_id' => $officer->id,
            'project_title' => $title,
            'description' => 'Project description.',
            'requested_budget' => 1000,
            'status' => 'Approved',
        ]);
    }

    private function comment(Proposal $proposal, User $author, string $text): void
    {
        ProposalComment::create([
            'proposal_id' => $proposal->id,
            'user_id' => $author->id,
            'comment' => $text,
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
