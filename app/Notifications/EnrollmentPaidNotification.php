<?php

namespace App\Notifications;

use App\Helpers\SscHelper;
use App\Notifications\Channels\FcmChannel;
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
        return ['database', 'broadcast', FcmChannel::class];
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

    public function toFcm($notifiable): array
    {
        return [
            'title' => 'Enrollment payment confirmed',
            'body' => "Your enrollment payment ({$this->payment->reference}) has been marked as paid.",
            'data' => [
                'type' => 'enrollment_paid',
                'id' => $this->payment->id,
                'url' => SscHelper::studentRoute('mobile.student.enrollment'),
            ],
        ];
    }
}
