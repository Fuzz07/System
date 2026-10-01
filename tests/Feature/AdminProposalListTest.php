<?php

namespace Tests\Feature;

use App\Models\Proposal;
use App\Models\ProposalComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminProposalListTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $officer;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->admin = $this->user('admin', 'Site Admin');
        $this->officer = $this->user('officer', 'Project Officer');
    }

    public function test_rejected_proposals_have_no_print_button(): void
    {
        $pending = $this->proposal('Pending');
        $approved = $this->proposal('Approved');
        $rejected = $this->proposal('Rejected');

        $this->list()
            ->assertSee('href="' . route('proposals.print', $pending) . '"', false)
            ->assertSee('href="' . route('proposals.print', $approved) . '"', false)
            ->assertDontSee('href="' . route('proposals.print', $rejected) . '"', false);
    }

    public function test_admin_can_read_the_comments_on_a_proposal(): void
    {
        $proposal = $this->proposal('Approved');
        $student = $this->user('student', 'Maria Santos');
        $comment = ProposalComment::create(['proposal_id' => $proposal->id, 'user_id' => $student->id, 'comment' => 'Please add more trash bins.']);
        ProposalComment::create(['proposal_id' => $proposal->id, 'user_id' => $this->officer->id, 'comment' => 'Noted, we will add them.', 'parent_id' => $comment->id]);

        $this->list()
            ->assertSee('data-bs-target="#feedbackModal' . $proposal->id . '"', false)
            ->assertSee('id="feedbackModal' . $proposal->id . '"', false)
            ->assertSeeInOrder(['Please add more trash bins.', 'Noted, we will add them.'])
            ->assertSee('Anonymous Student')
            ->assertDontSee('Maria Santos')
            ->assertSee('Project Officer');
    }

    public function test_a_proposal_without_comments_says_so(): void
    {
        $proposal = $this->proposal('Pending');

        $this->list()
            ->assertSee('id="feedbackModal' . $proposal->id . '"', false)
            ->assertSee('No feedback or suggestions on this proposal yet.');
    }

    private function list()
    {
        return $this->actingAs($this->admin)
            ->withViewErrors([])
            ->get(route('admin.proposals'))
            ->assertOk();
    }

    private function proposal(string $status): Proposal
    {
        return Proposal::create([
            'officer_id' => $this->officer->id,
            'project_title' => "{$status} project",
            'description' => 'Project description.',
            'requested_budget' => 1000,
            'approved_budget' => $status === 'Approved' ? 1000 : null,
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
