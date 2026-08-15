<?php

namespace App\Http\Controllers\Officer;

use App\Helpers\SscHelper;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::with('author')->orderByDesc('created_at')->get();
        return view('officer.announcements', compact('announcements'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|max:5120',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            try {
                $imagePath = SscHelper::uploadToCloudinary($request->file('image'), 'announcements');
            } catch (\Exception $e) {
                \Log::warning('Cloudinary upload failed for announcement image, falling back to local public disk: ' . $e->getMessage());
                $imagePath = $request->file('image')->store('announcements', 'public');
            }
        }

        Announcement::create([
            'title' => $request->title,
            'content' => $request->content,
            'image_path' => $imagePath,
            'created_by' => Auth::id(),
        ]);

        SscHelper::logActivity(Auth::id(), 'ANNOUNCEMENT_POST', "Posted: {$request->title}");
        return redirect()->route('officer.announcements')->with('success', 'Announcement posted successfully.');
    }

    public function destroy(Announcement $announcement)
    {
        if ($announcement->created_by !== Auth::id())
            abort(403);
        $announcement->delete();
        return redirect()->route('officer.announcements')->with('success', 'Announcement deleted.');
    }
}
