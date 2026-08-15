<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\SscHelper;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->input('category');

        $query = Announcement::with('author')->orderByDesc('created_at');
        if ($category) {
            $query->where('category', $category);
        }
        $announcements = $query->paginate(6)->withQueryString();

        return view('admin.announcements', compact('announcements', 'category'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => ['nullable', Rule::in(array_keys(Announcement::CATEGORIES))],
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
            'category' => $request->category ?? Announcement::CATEGORY_GENERAL,
            'created_by' => Auth::id(),
        ]);

        SscHelper::logActivity(Auth::id(), 'ANNOUNCEMENT_POST', "Admin posted: {$request->title}");
        return redirect()->route('admin.announcements')->with('success', 'Announcement posted successfully.');
    }

    public function update(Request $request, Announcement $announcement)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => ['nullable', Rule::in(array_keys(Announcement::CATEGORIES))],
            'image' => 'nullable|image|max:5120',
            'remove_image' => 'nullable|boolean',
        ]);

        $imagePath = $announcement->image_path;
        if ($request->hasFile('image')) {
            try {
                $imagePath = SscHelper::uploadToCloudinary($request->file('image'), 'announcements');
            } catch (\Exception $e) {
                \Log::warning('Cloudinary upload failed for announcement image, falling back to local public disk: ' . $e->getMessage());
                $imagePath = $request->file('image')->store('announcements', 'public');
            }
        } elseif ($request->boolean('remove_image')) {
            $imagePath = null;
        }

        $announcement->update([
            'title' => $request->title,
            'content' => $request->content,
            'image_path' => $imagePath,
            'category' => $request->category ?? Announcement::CATEGORY_GENERAL,
        ]);

        SscHelper::logActivity(Auth::id(), 'ANNOUNCEMENT_UPDATE', "Admin updated announcement ID: {$announcement->id}");
        return redirect()->route('admin.announcements')->with('success', 'Announcement updated successfully.');
    }

    public function destroy(Announcement $announcement)
    {
        SscHelper::logActivity(Auth::id(), 'ANNOUNCEMENT_DELETE', "Admin deleted announcement ID: {$announcement->id}");
        $announcement->delete();
        return redirect()->route('admin.announcements')->with('success', 'Announcement deleted successfully.');
    }
}
