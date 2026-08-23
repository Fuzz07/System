<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class NotificationController extends Controller
{
    /** How far back the bell looks for announcements. */
    private const ANNOUNCEMENT_WINDOW_DAYS = 60;

    /**
     * The bell feed: personal notifications and council announcements, merged.
     *
     * Two sources, because they are shaped differently. A database notification
     * belongs to one student and carries its own read_at. An announcement is a
     * single row the whole school shares, so there is nothing per-student to
     * mark — the student's notifications_seen_at timestamp decides whether it is
     * still new to them.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $seenAt = $user->notifications_seen_at;
        $mobile = $request->query('surface') === 'mobile';

        $personal = $user->notifications()
            ->orderByDesc('created_at')
            ->take(20)
            ->get()
            ->map(fn ($n) => [
                'kind'    => 'personal',
                'id'      => $n->id,
                'title'   => null,
                'message' => $n->data['message'] ?? 'You have a new update from the SSC.',
                'url'     => $n->data['url'] ?? null,
                'unread'  => is_null($n->read_at),
                'at'      => $n->created_at,
            ]);

        $announcementUrl = $mobile
            ? route('mobile.student.announcements')
            : route('student.announcements');

        $announcements = Announcement::with('author')
            ->where('created_at', '>=', now()->subDays(self::ANNOUNCEMENT_WINDOW_DAYS))
            ->orderByDesc('created_at')
            ->take(20)
            ->get()
            ->map(fn ($a) => [
                'kind'    => 'announcement',
                'id'      => 'ann-' . $a->id,
                'title'   => $a->title,
                'message' => Str::limit($a->content, 120),
                'url'     => $announcementUrl,
                'unread'  => is_null($seenAt) || $a->created_at?->greaterThan($seenAt),
                'at'      => $a->created_at,
            ]);

        $feed = $personal->concat($announcements)
            ->sortByDesc('at')
            ->take(25)
            ->values()
            ->map(fn ($item) => [
                'kind'    => $item['kind'],
                'id'      => $item['id'],
                'title'   => $item['title'],
                'message' => $item['message'],
                'url'     => $item['url'],
                'unread'  => $item['unread'],
                'ago'     => $item['at']?->diffForHumans(),
            ]);

        return response()->json($feed);
    }

    /**
     * Badge count: unread personal notifications plus announcements posted since
     * this student last opened the bell.
     */
    public function unreadCount()
    {
        $user = Auth::user();
        $seenAt = $user->notifications_seen_at;

        $personal = $user->unreadNotifications()->count();

        $announcements = Announcement::query()
            ->where('created_at', '>=', now()->subDays(self::ANNOUNCEMENT_WINDOW_DAYS))
            ->when($seenAt, fn ($q) => $q->where('created_at', '>', $seenAt))
            ->count();

        return response()->json(['unread' => $personal + $announcements]);
    }

    /**
     * Clear the badge. Opening the bell is what "I have seen these" means here,
     * so it both marks the personal notifications read and moves the announcement
     * watermark forward.
     */
    public function markAllRead()
    {
        $user = Auth::user();
        $user->unreadNotifications->markAsRead();
        $user->forceFill(['notifications_seen_at' => now()])->save();

        return response()->json(['unread' => 0]);
    }
}
