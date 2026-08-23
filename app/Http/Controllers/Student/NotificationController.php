<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * The student's recent notifications, flattened for the bell menu.
     *
     * Database notifications store their payload as a free-form `data` array, so
     * the shape is normalised here rather than in the view: the client only ever
     * sees a message, an optional link, and whether it is still unread.
     */
    public function index()
    {
        $notifications = Auth::user()->notifications()
            ->orderByDesc('created_at')
            ->take(20)
            ->get()
            ->map(fn ($n) => [
                'id'      => $n->id,
                'message' => $n->data['message'] ?? 'You have a new update from the SSC.',
                'url'     => $n->data['url'] ?? null,
                'unread'  => is_null($n->read_at),
                'ago'     => $n->created_at?->diffForHumans(),
            ]);

        return response()->json($notifications);
    }

    public function unreadCount()
    {
        return response()->json(['unread' => Auth::user()->unreadNotifications()->count()]);
    }

    /**
     * Clear the badge. Called when the student opens the bell, since opening it
     * is what "I have seen these" means here.
     */
    public function markAllRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json(['unread' => 0]);
    }
}
