<?php

namespace App\Http\Controllers\Student;

use App\Helpers\SscHelper;
use App\Http\Controllers\Controller;
use App\Models\Candidacy;
use App\Models\SchoolYear;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VotingController extends Controller
{
    public function index()
    {
        $student = Auth::user();
        $activeSy = SchoolYear::where('is_active', 1)->first();

        if (!$activeSy) {
            return view('student.voting', [
                'activeSy' => null,
                'candidatesByPosition' => [],
                'myVotes' => [],
            ]);
        }

        // Fetch all approved candidacies for the active school year
        $candidates = Candidacy::with('user')
            ->where('school_year', $activeSy->label)
            ->where('status', 'approved')
            ->get();

        // Group and filter candidates by position
        $candidatesByPosition = [];
        foreach ($candidates as $c) {
            $pos = $c->position;
            
            // If it is a representative position, check if it matches student's department
            if (str_ends_with($pos, ' Representative')) {
                $expectedPos = $student->department . ' Representative';
                if (strcasecmp($pos, $expectedPos) !== 0) {
                    continue; // Skip representative position of other departments
                }
            }

            $candidatesByPosition[$pos][] = $c;
        }

        // Get the logged in student's votes for this active school year
        $myVotes = Vote::where('user_id', $student->id)
            ->where('school_year', $activeSy->label)
            ->get()
            ->keyBy('position');

        return view('student.voting', compact('activeSy', 'candidatesByPosition', 'myVotes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'candidacy_id' => 'required|exists:candidacies,id',
        ]);

        $student = Auth::user();
        $activeSy = SchoolYear::where('is_active', 1)->first();

        if (!$activeSy) {
            return back()->with('danger', 'No active school year set. Voting is unavailable.');
        }

        // Fetch the candidacy
        $candidacy = Candidacy::where('id', $request->candidacy_id)
            ->where('school_year', $activeSy->label)
            ->where('status', 'approved')
            ->first();

        if (!$candidacy) {
            return back()->with('danger', 'Invalid or unapproved candidate selected.');
        }

        // If it's a representative position, make sure student is in that department
        if (str_ends_with($candidacy->position, ' Representative')) {
            $expectedPos = $student->department . ' Representative';
            if (strcasecmp($candidacy->position, $expectedPos) !== 0) {
                return back()->with('danger', 'You can only vote for the representative of your own department.');
            }
        }

        // Check if student has already voted for this position in this school year
        $alreadyVoted = Vote::where('user_id', $student->id)
            ->where('position', $candidacy->position)
            ->where('school_year', $activeSy->label)
            ->exists();

        if ($alreadyVoted) {
            return back()->with('danger', 'You have already cast a vote for this position.');
        }

        // Record the vote
        Vote::create([
            'user_id' => $student->id,
            'candidacy_id' => $candidacy->id,
            'position' => $candidacy->position,
            'school_year' => $activeSy->label,
        ]);

        SscHelper::logActivity(
            $student->id,
            'STUDENT_VOTE',
            "Cast a vote for {$candidacy->user->fullname} as {$candidacy->position}"
        );

        return redirect()->route('student.voting')->with('success', 'Your vote has been cast successfully!');
    }

    public function indexMobile()
    {
        $student = Auth::user();
        $activeSy = SchoolYear::where('is_active', 1)->first();

        if (!$activeSy) {
            return view('mobile.student.voting', [
                'activeSy' => null,
                'candidatesByPosition' => [],
                'myVotes' => [],
            ]);
        }

        $candidates = Candidacy::with('user')
            ->where('school_year', $activeSy->label)
            ->where('status', 'approved')
            ->get();

        $candidatesByPosition = [];
        foreach ($candidates as $c) {
            $pos = $c->position;
            
            if (str_ends_with($pos, ' Representative')) {
                $expectedPos = $student->department . ' Representative';
                if (strcasecmp($pos, $expectedPos) !== 0) {
                    continue;
                }
            }

            $candidatesByPosition[$pos][] = $c;
        }

        $myVotes = Vote::where('user_id', $student->id)
            ->where('school_year', $activeSy->label)
            ->get()
            ->keyBy('position');

        return view('mobile.student.voting', compact('activeSy', 'candidatesByPosition', 'myVotes'));
    }

    public function storeMobile(Request $request)
    {
        $request->validate([
            'candidacy_id' => 'required|exists:candidacies,id',
        ]);

        $student = Auth::user();
        $activeSy = SchoolYear::where('is_active', 1)->first();

        if (!$activeSy) {
            return back()->with('danger', 'No active school year set. Voting is unavailable.');
        }

        $candidacy = Candidacy::where('id', $request->candidacy_id)
            ->where('school_year', $activeSy->label)
            ->where('status', 'approved')
            ->first();

        if (!$candidacy) {
            return back()->with('danger', 'Invalid or unapproved candidate selected.');
        }

        if (str_ends_with($candidacy->position, ' Representative')) {
            $expectedPos = $student->department . ' Representative';
            if (strcasecmp($candidacy->position, $expectedPos) !== 0) {
                return back()->with('danger', 'You can only vote for the representative of your own department.');
            }
        }

        $alreadyVoted = Vote::where('user_id', $student->id)
            ->where('position', $candidacy->position)
            ->where('school_year', $activeSy->label)
            ->exists();

        if ($alreadyVoted) {
            return back()->with('danger', 'You have already cast a vote for this position.');
        }

        Vote::create([
            'user_id' => $student->id,
            'candidacy_id' => $candidacy->id,
            'position' => $candidacy->position,
            'school_year' => $activeSy->label,
        ]);

        SscHelper::logActivity(
            $student->id,
            'STUDENT_VOTE',
            "Cast a mobile vote for {$candidacy->user->fullname} as {$candidacy->position}"
        );

        return redirect()->route('mobile.student.voting')->with('success', 'Your vote has been cast successfully!');
    }

    public function results()
    {
        $activeSy = \App\Models\SchoolYear::where('is_active', 1)->first();

        if (!$activeSy) {
            return view('shared.election-results', [
                'activeSy' => null,
                'candidatesByPosition' => [],
            ]);
        }

        $candidates = Candidacy::with('user')
            ->withCount('votes')
            ->where('school_year', $activeSy->label)
            ->where('status', 'approved')
            ->get();

        $candidatesByPosition = [];
        foreach ($candidates as $c) {
            $candidatesByPosition[$c->position][] = $c;
        }

        foreach ($candidatesByPosition as $pos => &$cands) {
            usort($cands, function ($a, $b) {
                return $b->votes_count <=> $a->votes_count;
            });
        }

        return view('shared.election-results', compact('activeSy', 'candidatesByPosition'));
    }
}
