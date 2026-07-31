<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class ElectionOpenNotification extends Notification
{
    use Queueable;

    protected $startsAt;
    protected $endsAt;

    public function __construct($startsAt, $endsAt)
    {
        $this->startsAt = $startsAt;
        $this->endsAt = $endsAt;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => 'SSC Elections are now OPEN! Cast your votes within the next 8 hours.',
            'starts_at' => $this->startsAt,
            'ends_at' => $this->endsAt,
            'url' => route('student.voting'),
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'message' => 'SSC Elections are now OPEN! Cast your votes within the next 8 hours.',
            'starts_at' => $this->startsAt,
            'ends_at' => $this->endsAt,
            'url' => route('student.voting'),
        ]);
    }
}
