<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Proposal;
use App\Models\Candidacy;
use App\Models\SchoolYear;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $student = Auth::user();
        
        // Get latest announcements
        $announcements = Announcement::with('officer')
            ->orderByDesc('created_at')
            ->limit(3)
            ->get();
        
        // Get pending proposals
        $pendingProposals = Proposal::whereIn('status', ['Pending', 'Approved'])
            ->with('officer')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
        
        // Check if student has active candidacy
        $activeSy = SchoolYear::where('is_active', 1)->first();
        $activeCandidacy = null;
        if ($activeSy) {
            $activeCandidacy = Candidacy::where('user_id', $student->id)
                ->where('school_year', $activeSy->label)
                ->first();
        }
        
        return view('student.overview', compact(
            'announcements',
            'pendingProposals',
            'activeCandidacy',
            'activeSy'
        ));
    }
}
