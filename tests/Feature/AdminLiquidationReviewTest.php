<?php

namespace Tests\Feature;

use App\Models\Liquidation;
use App\Models\Proposal;
use App\Models\User;
use App\Notifications\AdminAlertNotification;
use App\Notifications\LiquidationReviewedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminLiquidationReviewTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $officer;
    private Liquidation $liquidation;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();

        $this->admin = $this->user('admin', 'Council Administrator');
        $this->officer = $this->user('officer', 'Project Officer');
        $proposal = Proposal::create([
            'officer_id' => $this->officer->id,
            'project_title' => 'Community Outreach',
            'requested_budget' => 5000,
            'approved_budget' => 5000,
            'description' => 'Community project.',
            'status' => 'Approved',
        ]);
        $this->liquidation = Liquidation::create([
            'proposal_id' => $proposal->id,
            'officer_id' => $this->officer->id,
            'title' => 'Outreach liquidation',
            'file_path' => 'liquidation/outreach.jpg',
            'notes' => 'Receipts attached.',
        ]);
    }

    public function test_pending_report_is_listed_for_admin_review_and_admin_is_alerted_on_submission(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.liquidations'))
            ->assertOk()
            ->assertSee('Outreach liquidation')
            ->assertSee('Review')
            ->assertSee(route('admin.liquidations.review', $this->liquidation), false);

        Notification::assertSentTo(
            $this->admin,
            AdminAlertNotification::class,
            fn (AdminAlertNotification $notification): bool => $notification->type === 'liquidation_report'
                && $notification->url === route('admin.liquidations', ['status' => 'Pending'])
        );
    }

    public function test_admin_can_approve_a_pending_report_and_the_officer_is_notified(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.liquidations.review', $this->liquidation), [
                'action' => 'approve',
                'review_notes' => 'Receipts verified.',
            ])
            ->assertRedirect(route('admin.liquidations'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('liquidations', [
            'id' => $this->liquidation->id,
            'status' => 'Approved',
            'reviewed_by' => $this->admin->id,
            'review_notes' => 'Receipts verified.',
        ]);

        Notification::assertSentTo(
            $this->officer,
            LiquidationReviewedNotification::class,
            fn (LiquidationReviewedNotification $notification): bool => $notification->toDatabase($this->officer)['title'] === 'Liquidation report approved'
        );
    }

    public function test_admin_must_include_a_reason_to_reject_and_officer_can_see_it(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.liquidations.review', $this->liquidation), ['action' => 'reject'])
            ->assertSessionHasErrors('review_notes');

        $this->assertSame('Pending', $this->liquidation->fresh()->status);

        $this->post(route('admin.liquidations.review', $this->liquidation), [
            'action' => 'reject',
            'review_notes' => 'Please include the missing receipt.',
        ])->assertRedirect(route('admin.liquidations'));

        $this->actingAs($this->officer)
            ->get(route('officer.liquidation'))
            ->assertOk()
            ->assertSee('Please include the missing receipt.');
    }

    public function test_only_admins_can_review_and_a_report_cannot_be_reviewed_twice(): void
    {
        $this->actingAs($this->officer)
            ->post(route('admin.liquidations.review', $this->liquidation), ['action' => 'approve'])
            ->assertForbidden();

        $this->actingAs($this->admin)
            ->post(route('admin.liquidations.review', $this->liquidation), ['action' => 'approve'])
            ->assertRedirect(route('admin.liquidations'));

        $this->post(route('admin.liquidations.review', $this->liquidation), ['action' => 'reject', 'review_notes' => 'Not eligible.'])
            ->assertForbidden();
    }

    private function user(string $role, string $name): User
    {
        return User::create([
            'fullname' => $name,
            'email' => str_replace(' ', '.', strtolower($name)) . '@example.test',
            'password' => 'password',
            'role' => $role,
            'status' => 'active',
        ]);
    }
}
