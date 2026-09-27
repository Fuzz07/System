<?php

namespace App\Notifications\Channels;

use App\Services\PushNotificationService;
use Illuminate\Notifications\Notification;

/**
 * Delivers a notification to the student's phone through Firebase Cloud
 * Messaging, so it lands in the system tray even while the app is closed.
 *
 * A notification opts in by listing this class in via() and defining
 * toFcm($notifiable), which returns ['title' => ..., 'body' => ..., 'data' => [...]].
 * Put 'url' in the data to open that page when the notification is tapped.
 *
 * List it after 'database' so the bell copy is saved even if the push fails.
 */
class FcmChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        $message = $notification->toFcm($notifiable);

        PushNotificationService::sendToUsers(
            [$notifiable->getKey()],
            $message['title'],
            $message['body'],
            $message['data'] ?? []
        );
    }
}
