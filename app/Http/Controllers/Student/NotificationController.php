<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $notifications = $user->notifications()->orderByDesc('created_at')->take(20)->get();
        return response()->json($notifications);
    }

    public function unreadCount()
    {
        $user = Auth::user();
        return response()->json(['unread' => $user->unreadNotifications()->count()]);
    }
}
