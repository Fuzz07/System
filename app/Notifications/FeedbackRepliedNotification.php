<?php

namespace App\Notifications;

use App\Models\Feedback;
use App\Helpers\SscHelper;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * Tells a student the council has answered their feedback: an inbox copy for
 * the notification bell, and a push that carries the reply itself so it can be
 * read from the lock screen without opening the app.
 */
class FeedbackRepliedNotification extends Notification
{
    use Queueable;

    protected $feedback;

    public function __construct(Feedback $feedback)
    {
        $this->feedback = $feedback;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast', FcmChannel::class];
    }

    protected function payload(): array
    {
        return [
            'message'     => 'The SSC replied to your feedback: ' . Str::limit($this->feedback->reply, 120),
            'feedback_id' => $this->feedback->id,
            'url'         => SscHelper::studentRoute('student.feedback'),
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
            'title' => 'The SSC replied to your feedback',
            'body' => Str::limit($this->feedback->reply, 160),
            'data' => [
                'type' => 'feedback_reply',
                'id' => $this->feedback->id,
                'reply' => $this->feedback->reply,
                'replied_by' => $this->feedback->replier->fullname ?? 'The SSC',
                'url' => SscHelper::studentRoute('mobile.student.feedback'),
            ],
        ];
    }
}
