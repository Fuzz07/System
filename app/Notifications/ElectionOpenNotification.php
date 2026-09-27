<?php

namespace App\Notifications;

use App\Helpers\SscHelper;
use App\Notifications\Channels\FcmChannel;
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
        return ['database', 'broadcast', FcmChannel::class];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => 'SSC Elections are now OPEN! Cast your votes within the next 8 hours.',
            'starts_at' => $this->startsAt,
            'ends_at' => $this->endsAt,
            'url' => SscHelper::studentRoute('student.voting'),
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'message' => 'SSC Elections are now OPEN! Cast your votes within the next 8 hours.',
            'starts_at' => $this->startsAt,
            'ends_at' => $this->endsAt,
            'url' => SscHelper::studentRoute('student.voting'),
        ]);
    }

    public function toFcm($notifiable): array
    {
        return [
            'title' => 'SSC Elections are OPEN!',
            'body' => "Cast your votes between {$this->startsAt->format('h:i A')} and {$this->endsAt->format('h:i A')}. 1-minute limit per position!",
            'data' => [
                'type' => 'election_open',
                'url' => SscHelper::studentRoute('mobile.student.voting'),
            ],
        ];
    }
}
