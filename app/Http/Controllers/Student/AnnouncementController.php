<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->input('category');

        $query = Announcement::with(['author', 'proposal'])->orderByDesc('created_at');
        if ($category) {
            $query->where('category', $category);
        }
        $announcements = $query->get();

        return view('student.announcements', compact('announcements', 'category'));
    }
}
