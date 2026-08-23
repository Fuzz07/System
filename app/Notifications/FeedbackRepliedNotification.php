<?php

namespace App\Notifications;

use App\Models\Feedback;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * The in-app record of a reply, so it shows in the notification bell alongside
 * enrolment and election updates. The FCM push is sent separately and carries
 * the same reply to the phone's lock screen; this is the inbox copy.
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
        return ['database', 'broadcast'];
    }

    protected function payload(): array
    {
        return [
            'message'     => 'The SSC replied to your feedback: ' . Str::limit($this->feedback->reply, 120),
            'feedback_id' => $this->feedback->id,
            'url'         => route('student.feedback'),
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
}
