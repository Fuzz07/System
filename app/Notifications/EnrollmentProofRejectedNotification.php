<?php

namespace App\Notifications;

use App\Models\EnrollmentPayment;
use App\Helpers\SscHelper;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells a student the proof of payment they uploaded was rejected, with the
 * reviewer's note when there is one, so they know to upload a new one.
 */
class EnrollmentProofRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(protected EnrollmentPayment $payment) {}

    public function via($notifiable)
    {
        return ['database', 'broadcast', FcmChannel::class];
    }

    protected function message(): string
    {
        $message = "Your contribution fee payment proof ({$this->payment->reference}) was rejected. Please upload a new one.";

        if (filled($this->payment->proof_notes)) {
            $message .= " Reason: {$this->payment->proof_notes}";
        }

        return $message;
    }

    protected function payload(): array
    {
        return [
            'message' => $this->message(),
            'payment_id' => $this->payment->id,
            'url' => SscHelper::studentRoute('student.enrollment.index'),
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
            'title' => 'Payment proof rejected',
            'body' => $this->message(),
            'data' => [
                'type' => 'enrollment_proof_rejected',
                'id' => $this->payment->id,
                'url' => SscHelper::studentRoute('mobile.student.enrollment'),
            ],
        ];
    }
}
