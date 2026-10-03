<?php

namespace Tests\Feature;

use App\Models\Proposal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProposalNavigationAndReceiptTest extends TestCase
{
    use RefreshDatabase;

    public function test_print_page_has_a_reliable_back_link_for_each_staff_portal(): void
    {
        $officer = $this->user('officer', 'Project Officer');
        $proposal = $this->proposal($officer);

        $this->actingAs($officer)
            ->get(route('proposals.print', $proposal))
            ->assertOk()
            ->assertSee('href="' . route('officer.proposals') . '"', false)
            ->assertDontSee('javascript:history.back()', false);

        $admin = $this->user('admin', 'Site Admin');
        $this->actingAs($admin)
            ->get(route('proposals.print', $proposal))
            ->assertOk()
            ->assertSee('href="' . route('admin.proposals') . '"', false);

        $treasurer = $this->user('treasurer', 'SSC Treasurer');
        $this->actingAs($treasurer)
            ->get(route('proposals.print', $proposal))
            ->assertOk()
            ->assertSee('href="' . route('officer.proposals') . '"', false);
    }

    public function test_project_receipt_opens_in_a_modal_on_desktop_and_mobile(): void
    {
        $officer = $this->user('officer', 'Project Officer');
        $student = $this->user('student', 'Student Viewer');
        $proposal = $this->proposal($officer, [
            'project_status' => 'Completed',
            'completion_proof' => 'receipts/project-receipt.jpg',
        ]);
        $modalId = 'proposalReceiptModal' . $proposal->id;
        $receiptUrl = asset('storage/receipts/project-receipt.jpg');

        $this->actingAs($student)
            ->get(route('student.proposal.show', $proposal))
            ->assertOk()
            ->assertSee('data-bs-target="#' . $modalId . '"', false)
            ->assertSee('id="' . $modalId . '"', false)
            ->assertSee('<img src="' . $receiptUrl . '"', false)
            ->assertSee('Open full size');

        $this->actingAs($student)
            ->get(route('mobile.student.proposal.show', $proposal))
            ->assertOk()
            ->assertSee('data-receipt-modal-open="' . $modalId . '"', false)
            ->assertSee('id="' . $modalId . '"', false)
            ->assertSee('<img src="' . $receiptUrl . '"', false)
            ->assertSee('role="dialog"', false)
            ->assertSee('function openReceipt()', false);
    }

    private function proposal(User $officer, array $attributes = []): Proposal
    {
        return Proposal::create(array_merge([
            'officer_id' => $officer->id,
            'project_title' => 'Community Outreach',
            'description' => 'A community project.',
            'requested_budget' => 1000,
            'approved_budget' => 1000,
            'status' => 'Approved',
            'project_status' => 'Ongoing',
        ], $attributes));
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
