<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->input('category');

        $query = Announcement::with(['author', 'proposal', 'comments.user'])->orderByDesc('created_at');
        if ($category) {
            $query->where('category', $category);
        }
        $announcements = $query->get();

        return view('student.announcements', compact('announcements', 'category'));
    }

    public function comment(Request $request, Announcement $announcement)
    {
        // Comments are only open on Lost & Found posts — general announcements
        // stay one-way broadcasts, consistent with how they work everywhere else.
        if ($announcement->category !== Announcement::CATEGORY_LOST_ITEM) {
            abort(404);
        }

        $request->validate(['comment' => 'required|string|min:1|max:2000']);

        $announcement->comments()->create([
            'user_id' => Auth::id(),
            'comment' => $request->comment,
        ]);

        return redirect()->back()->with('success', 'Comment added!');
    }
}
