<?php

namespace Tests\Feature;

use App\Models\EnrollmentPayment;
use App\Models\Proposal;
use App\Models\User;
use App\Notifications\AdminAlertNotification;
use App\Services\AdminAlertService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminEmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_proposal_request_notifies_every_active_admin_by_database_and_email(): void
    {
        Notification::fake();

        $firstAdmin = $this->createUser('admin', 'active', 'admin-one@example.test');
        $secondAdmin = $this->createUser('admin', 'active', 'admin-two@example.test');
        $inactiveAdmin = $this->createUser('admin', 'inactive', 'inactive-admin@example.test');
        $officer = $this->createUser('officer', 'active', 'officer@example.test');

        Proposal::create([
            'officer_id' => $officer->id,
            'project_title' => 'Student Leadership Summit',
            'requested_budget' => 25000,
            'description' => 'A leadership summit proposal requiring approval.',
        ]);

        foreach ([$firstAdmin, $secondAdmin] as $admin) {
            Notification::assertSentTo(
                $admin,
                AdminAlertNotification::class,
                function (AdminAlertNotification $notification, array $channels) use ($admin): bool {
                    $mail = $notification->toMail($admin);

                    return $notification->type === 'proposal_approval'
                        && $notification->title === 'Proposal approval requested'
                        && in_array('database', $channels, true)
                        && in_array('mail', $channels, true)
                        && $mail->subject === '[SSC Admin] Proposal approval requested'
                        && str_contains($mail->actionUrl, '/proposals');
                }
            );
        }

        Notification::assertNotSentTo($inactiveAdmin, AdminAlertNotification::class);
        Notification::assertNotSentTo($officer, AdminAlertNotification::class);
    }

    public function test_inactive_student_registration_sends_an_admin_approval_alert(): void
    {
        Notification::fake();
        $admin = $this->createUser('admin', 'active', 'admin@example.test');

        $student = $this->createUser('student', 'inactive', 'student@example.test');

        Notification::assertSentTo(
            $admin,
            AdminAlertNotification::class,
            fn (AdminAlertNotification $notification) => $notification->type === 'student_approval'
                && str_contains($notification->message, $student->fullname)
                && str_contains($notification->url, '/students')
        );
    }

    public function test_enrollment_proof_alert_is_sent_once_when_the_proof_changes(): void
    {
        Notification::fake();
        $admin = $this->createUser('admin', 'active', 'admin@example.test');
        $student = $this->createUser('student', 'active', 'student@example.test');

        $payment = EnrollmentPayment::create([
            'user_id' => $student->id,
            'amount' => 50,
            'semester' => '2026-2027 - 1st Semester',
            'method' => 'gcash',
            'status' => 'pending',
            'reference' => 'GCASH-TEST',
            'proof_status' => 'pending',
        ]);

        $payment->update(['proof_path' => 'enrollment_proofs/proof.jpg']);
        $payment->update(['method' => 'instapay']);

        Notification::assertSentToTimes($admin, AdminAlertNotification::class, 1);
        Notification::assertSentTo(
            $admin,
            AdminAlertNotification::class,
            fn (AdminAlertNotification $notification) => $notification->type === 'enrollment_proof'
        );
    }

    public function test_admin_alert_is_stored_in_the_bell_feed_when_mail_is_enabled(): void
    {
        $admin = $this->createUser('admin', 'active', 'admin@example.test');

        AdminAlertService::send(
            'Approval needed',
            'A request is waiting for review.',
            route('admin.dashboard'),
            'approval_test'
        );

        $notification = $admin->notifications()->first();

        $this->assertNotNull($notification);
        $this->assertSame('Approval needed', $notification->data['title']);
        $this->assertSame('approval_test', $notification->data['type']);
    }

    private function createUser(string $role, string $status, string $email): User
    {
        return User::create([
            'fullname' => ucfirst($role) . ' Test User',
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => $role,
            'status' => $status,
        ]);
    }
}
