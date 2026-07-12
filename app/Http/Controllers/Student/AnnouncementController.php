<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Announcement;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::with(['author', 'proposal'])
            ->orderByDesc('created_at')
            ->get();
        return view('student.announcements', compact('announcements'));
    }
}
