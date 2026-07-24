<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    public function index()
    {
        $feedbacks = Feedback::with(['student', 'replier'])->orderByDesc('created_at')->get();
        return view('admin.feedback', compact('feedbacks'));
    }

    public function reply(Request $request, Feedback $feedback)
    {
        $request->validate(['reply' => 'required|string']);
        $feedback->update([
            'reply'      => $request->reply,
            'replied_by' => Auth::id(),
            'status'     => 'Replied',
        ]);
        return redirect()->route('admin.feedback')->with('success', 'Reply sent successfully.');
    }
}
