<?php

namespace App\Notifications;

use App\Models\Candidacy;
use App\Helpers\SscHelper;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells a student their dean has approved or declined their candidacy filing.
 */
class CandidacyReviewedNotification extends Notification
{
    use Queueable;

    public function __construct(protected Candidacy $candidacy) {}

    public function via($notifiable)
    {
        return ['database', 'broadcast', FcmChannel::class];
    }

    protected function approved(): bool
    {
        return $this->candidacy->status === 'approved';
    }

    protected function message(): string
    {
        return $this->approved()
            ? "Your candidacy for {$this->candidacy->position} has been approved. You are on the ballot!"
            : "Your candidacy for {$this->candidacy->position} was not approved by your dean.";
    }

    protected function payload(): array
    {
        return [
            'message' => $this->message(),
            'candidacy_id' => $this->candidacy->id,
            'status' => $this->candidacy->status,
            'url' => SscHelper::studentRoute('student.candidacy'),
        ];
    }

    public function toDatabase($notifiable)
    {
        return $this->payload();
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->payload());
    }

    public function toFcm($notifiable): array
    {
        return [
            'title' => $this->approved() ? 'Candidacy approved' : 'Candidacy not approved',
            'body' => $this->message(),
            'data' => [
                'type' => 'candidacy_reviewed',
                'id' => $this->candidacy->id,
                'status' => $this->candidacy->status,
                'url' => SscHelper::studentRoute('mobile.student.candidacy'),
            ],
        ];
    }
}
