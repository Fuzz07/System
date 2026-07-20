<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use App\Models\EnrollmentPayment;

class EnrollmentPaidNotification extends Notification
{
    use Queueable;

    protected $payment;

    public function __construct(EnrollmentPayment $payment)
    {
        $this->payment = $payment;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => "Your enrollment payment ({$this->payment->reference}) has been marked as paid.",
            'payment_id' => $this->payment->id,
            'amount' => $this->payment->amount,
            'paid_at' => $this->payment->paid_at,
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'message' => "Your enrollment payment ({$this->payment->reference}) has been marked as paid.",
            'payment_id' => $this->payment->id,
            'amount' => $this->payment->amount,
            'paid_at' => $this->payment->paid_at,
        ]);
    }
}
