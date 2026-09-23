<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NotificationController extends Controller
{
    private const ANNOUNCEMENT_WINDOW_DAYS = 60;

    public function index(Request $request)
    {
        $user = $request->user();
        $seenAt = $user->notifications_seen_at;

        $personal = $user->notifications()
            ->orderByDesc('created_at')
            ->take(20)
            ->get()
            ->map(fn ($notification) => [
                'kind' => 'personal',
                'id' => $notification->id,
                'title' => $notification->data['title'] ?? null,
                'message' => $notification->data['message'] ?? 'You have a new update from the SSC.',
                'url' => $notification->data['url'] ?? null,
                'unread' => is_null($notification->read_at),
                'at' => $notification->created_at,
            ]);

        $announcementUrl = $this->announcementUrl($request);
        $announcements = Announcement::query()
            ->where('created_at', '>=', now()->subDays(self::ANNOUNCEMENT_WINDOW_DAYS))
            ->orderByDesc('created_at')
            ->take(20)
            ->get()
            ->map(fn ($announcement) => [
                'kind' => 'announcement',
                'id' => 'ann-' . $announcement->id,
                'title' => $announcement->title,
                'message' => Str::limit($announcement->content, 120),
                'url' => $announcementUrl,
                'unread' => is_null($seenAt) || $announcement->created_at?->greaterThan($seenAt),
                'at' => $announcement->created_at,
            ]);

        $feed = $personal->concat($announcements)
            ->sortByDesc('at')
            ->take(25)
            ->values()
            ->map(fn ($item) => [
                'kind' => $item['kind'],
                'id' => $item['id'],
                'title' => $item['title'],
                'message' => $item['message'],
                'url' => $item['url'],
                'unread' => $item['unread'],
                'ago' => $item['at']?->diffForHumans(),
            ]);

        return response()->json($feed);
    }

    public function unreadCount(Request $request)
    {
        $user = $request->user();
        $seenAt = $user->notifications_seen_at;
        $personal = $user->unreadNotifications()->count();
        $announcements = Announcement::query()
            ->where('created_at', '>=', now()->subDays(self::ANNOUNCEMENT_WINDOW_DAYS))
            ->when($seenAt, fn ($query) => $query->where('created_at', '>', $seenAt))
            ->count();

        return response()->json(['unread' => $personal + $announcements]);
    }

    public function markAllRead(Request $request)
    {
        $user = $request->user();
        $user->unreadNotifications->markAsRead();
        $user->forceFill(['notifications_seen_at' => now()])->save();

        return response()->json(['unread' => 0]);
    }

    private function announcementUrl(Request $request): ?string
    {
        if ($request->query('surface') === 'mobile' && $request->user()->isStudent()) {
            return route('mobile.student.announcements');
        }

        return match (true) {
            $request->routeIs('admin.*') => route('admin.announcements'),
            $request->routeIs('officer.*') => route('officer.announcements'),
            $request->routeIs('treasurer.*') => route('treasurer.announcements'),
            $request->routeIs('dean.*') => route('dean.dashboard'),
            $request->user()->isStudent() => route('student.announcements'),
            default => null,
        };
    }
}
