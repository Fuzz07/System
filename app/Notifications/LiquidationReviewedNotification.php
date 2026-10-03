<?php

namespace App\Notifications;

use App\Models\Liquidation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LiquidationReviewedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Liquidation $liquidation) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Liquidation report ' . strtolower($this->liquidation->status),
            'message' => $this->message(),
            'url' => route('officer.liquidation'),
            'type' => 'liquidation_review',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Liquidation report ' . strtolower($this->liquidation->status))
            ->greeting('Hello ' . $notifiable->fullname . ',')
            ->line($this->message())
            ->action('View Liquidation Reports', route('officer.liquidation'));
    }

    private function message(): string
    {
        $message = "Your liquidation report \"{$this->liquidation->title}\" was "
            . strtolower($this->liquidation->status) . '.';

        if (filled($this->liquidation->review_notes)) {
            $message .= ' Review note: ' . $this->liquidation->review_notes;
        }

        return $message;
    }
}
