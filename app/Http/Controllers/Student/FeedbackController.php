<?php

namespace App\Http\Controllers\Student;

use App\Helpers\SscHelper;
use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    public function index()
    {
        $feedbacks = Feedback::with('replier')
            ->where('student_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();
        return view('student.feedback', compact('feedbacks'));
    }

    public function store(Request $request)
    {
        $request->validate(['message' => 'required|string']);
        Feedback::create([
            'student_id' => Auth::id(),
            'message'    => $request->message,
        ]);
        SscHelper::logActivity(Auth::id(), 'FEEDBACK_SUBMIT', 'Student submitted feedback');
        return redirect()->route('student.feedback')->with('success', 'Feedback submitted. We will respond within 3-5 working days.');
    }
}
