<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AnnouncementComment;
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
        // Open on every category: Lost & Found posts collect leads, general ones
        // collect feedback that the posting officer reads on their own page.
        $request->validate(['comment' => 'required|string|min:1|max:2000']);

        $announcement->comments()->create([
            'user_id' => Auth::id(),
            'comment' => $request->comment,
        ]);

        return redirect()->back()->with('success', 'Comment added!');
    }

    public function reply(Request $request, Announcement $announcement, AnnouncementComment $comment)
    {
        $request->validate(['comment' => 'required|string|min:1|max:2000']);

        $announcement->comments()->create([
            'user_id' => Auth::id(),
            'comment' => $request->comment,
            // Threads are one level deep: replying to a reply joins its thread.
            'parent_id' => $comment->parent_id ?? $comment->id,
        ]);

        return redirect()->back()->with('success', 'Reply posted!');
    }

    public function updateComment(Request $request, Announcement $announcement, AnnouncementComment $comment)
    {
        // Students may reword or remove their own comments, never anyone else's.
        abort_unless((int) $comment->user_id === (int) Auth::id(), 403);

        $request->validate(['comment' => 'required|string|min:1|max:2000']);
        $comment->update(['comment' => $request->comment]);

        return redirect()->back()->with('success', 'Comment updated.');
    }

    public function destroyComment(Announcement $announcement, AnnouncementComment $comment)
    {
        abort_unless((int) $comment->user_id === (int) Auth::id(), 403);

        $comment->delete();

        return redirect()->back()->with('success', 'Comment deleted.');
    }
}
