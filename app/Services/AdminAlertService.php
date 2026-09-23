<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\AdminAlertNotification;
use Illuminate\Support\Facades\Log;
use Throwable;

class AdminAlertService
{
    public static function send(string $title, string $message, string $url, string $type = 'admin_alert'): void
    {
        User::query()
            ->where('role', 'admin')
            ->where('status', 'active')
            ->get()
            ->each(function (User $admin) use ($title, $message, $url, $type): void {
                try {
                    $admin->notify(new AdminAlertNotification($title, $message, $url, $type));
                } catch (Throwable $exception) {
                    Log::error('Unable to deliver an admin alert.', [
                        'admin_id' => $admin->id,
                        'type' => $type,
                        'message' => $exception->getMessage(),
                    ]);
                }
            });
    }
}
