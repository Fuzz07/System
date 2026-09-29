<?php

namespace App\Notifications;

use App\Models\AnnouncementComment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * Tells whoever posted an announcement that a student has commented on it, so
 * the feedback reaches them through the notification bell. Students comment
 * anonymously, so the message never names the commenter.
 */
class AnnouncementCommentedNotification extends Notification
{
    use Queueable;

    public function __construct(protected AnnouncementComment $comment) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        $announcement = $this->comment->announcement;

        return [
            'title'           => 'New comment on your announcement',
            'message'         => 'A student commented on "' . Str::limit($announcement->title, 60) . '": ' . Str::limit($this->comment->comment, 100),
            'announcement_id' => $announcement->id,
            'comment_id'      => $this->comment->id,
            'url'             => $this->url($notifiable),
        ];
    }

    /**
     * The page where the author can read the thread: officers (and treasurers,
     * who post through the officer portal) land on their own posts, admins on
     * the announcements they manage.
     */
    protected function url($notifiable): ?string
    {
        return match ($notifiable->role) {
            'officer', 'treasurer' => route('officer.announcements', ['mine' => 1]),
            'admin'                => route('admin.announcements'),
            default                => null,
        };
    }
}
