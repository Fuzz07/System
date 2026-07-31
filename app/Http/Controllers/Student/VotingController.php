<?php

namespace App\Http\Controllers\Student;

use App\Helpers\SscHelper;
use App\Http\Controllers\Controller;
use App\Models\Candidacy;
use App\Models\SchoolYear;
use App\Models\StudentBallot;
use App\Models\Vote;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VotingController extends Controller
{
    public function index()
    {
        $student = Auth::user();
        $activeSy = SchoolYear::where('is_active', 1)->first();

        if (!$activeSy || !$activeSy->voting_open) {
            return view('student.voting_closed', compact('activeSy'));
        }

        $repPosition = $student->department . ' Representative';
        
        $allPositions = [
            'SSC President',
            'SSC Vice President',
            'SSC Secretary',
            'SSC Treasurer',
            $repPosition
        ];

        // Only include positions that have approved candidates
        $positionsWithCandidates = [];
        foreach ($allPositions as $pos) {
            $hasCands = Candidacy::where('school_year', $activeSy->label)
                ->where('status', 'approved')
                ->where('position', $pos)
                ->exists();
            if ($hasCands) {
                $positionsWithCandidates[] = $pos;
            }
        }

        $ballots = StudentBallot::where('user_id', $student->id)
            ->where('school_year', $activeSy->label)
            ->get()
            ->keyBy('position');

        $currentPosition = null;
        $currentBallot = null;
        $secondsRemaining = 0;

        foreach ($positionsWithCandidates as $pos) {
            $ballot = $ballots->get($pos);
            if (!$ballot) {
                $currentPosition = $pos;
                break;
            }

            if ($ballot->submitted_at) {
                continue;
            }

            $elapsed = now()->timestamp - $ballot->started_at->timestamp;
            if ($elapsed >= 60) {
                // Auto-expire
                $ballot->update(['submitted_at' => now()]);
                continue;
            }

            $currentPosition = $pos;
            $currentBallot = $ballot;
            $secondsRemaining = 60 - $elapsed;
            break;
        }

        if (!$currentPosition) {
            $myVotes = Vote::with('candidacy.user')
                ->where('user_id', $student->id)
                ->where('school_year', $activeSy->label)
                ->get();
            return view('student.voting_completed', compact('activeSy', 'myVotes', 'positionsWithCandidates'));
        }

        if (!$currentBallot) {
            return view('student.voting_prestart', compact('activeSy', 'currentPosition'));
        }

        $candidates = Candidacy::with('user')
            ->where('school_year', $activeSy->label)
            ->where('status', 'approved')
            ->where('position', $currentPosition)
            ->get();

        return view('student.voting_active', compact(
            'activeSy',
            'currentPosition',
            'candidates',
            'secondsRemaining',
            'currentBallot'
        ));
    }

    public function startBallot(Request $request)
    {
        $request->validate([
            'position' => 'required|string',
        ]);

        $student = Auth::user();
        $activeSy = SchoolYear::where('is_active', 1)->first();

        if (!$activeSy || !$activeSy->voting_open) {
            return back()->with('danger', 'Voting period is not active.');
        }

        // Check if ballot already exists
        $exists = StudentBallot::where('user_id', $student->id)
            ->where('school_year', $activeSy->label)
            ->where('position', $request->position)
            ->exists();

        if ($exists) {
            return back()->with('danger', 'You have already started or cast a ballot for this position.');
        }

        StudentBallot::create([
            'user_id' => $student->id,
            'position' => $request->position,
            'school_year' => $activeSy->label,
            'started_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        SscHelper::logActivity(
            $student->id,
            'VOTING_TIMER_START',
            "Started 1-minute voting countdown for position: {$request->position}"
        );

        return redirect()->route('student.voting');
    }

    public function castVote(Request $request)
    {
        $request->validate([
            'candidacy_id' => 'required|integer|exists:candidacies,id',
        ]);

        $student = Auth::user();
        $activeSy = SchoolYear::where('is_active', 1)->first();

        if (!$activeSy || !$activeSy->voting_open) {
            return redirect()->route('student.voting')->with('danger', 'Voting period is not active.');
        }

        $candidacy = Candidacy::where('id', $request->candidacy_id)
            ->where('school_year', $activeSy->label)
            ->where('status', 'approved')
            ->first();

        if (!$candidacy) {
            return redirect()->route('student.voting')->with('danger', 'Selected candidate is invalid.');
        }

        $ballot = StudentBallot::where('user_id', $student->id)
            ->where('school_year', $activeSy->label)
            ->where('position', $candidacy->position)
            ->first();

        if (!$ballot) {
            return redirect()->route('student.voting')->with('danger', 'Ballot session was not initialized.');
        }

        if ($ballot->submitted_at) {
            return redirect()->route('student.voting')->with('danger', 'You have already submitted your ballot for this position.');
        }

        $elapsed = now()->timestamp - $ballot->started_at->timestamp;
        if ($elapsed >= 60) {
            $ballot->update(['submitted_at' => now()]);
            return redirect()->route('student.voting')->with('danger', 'Your 1-minute voting limit for this position has expired.');
        }

        // Verify duplicate votes
        $voteExists = Vote::where('user_id', $student->id)
            ->where('position', $candidacy->position)
            ->where('school_year', $activeSy->label)
            ->exists();

        if ($voteExists) {
            $ballot->update(['submitted_at' => now()]);
            return redirect()->route('student.voting')->with('danger', 'You have already voted for this position.');
        }

        // Create the vote with IP and User Agent logging
        Vote::create([
            'user_id' => $student->id,
            'candidacy_id' => $candidacy->id,
            'position' => $candidacy->position,
            'school_year' => $activeSy->label,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $ballot->update([
            'submitted_at' => now(),
        ]);

        SscHelper::logActivity(
            $student->id,
            'STUDENT_VOTE_CAST',
            "Cast vote for {$candidacy->user->fullname} as {$candidacy->position} | IP: {$request->ip()}"
        );

        return redirect()->route('student.voting')->with('success', "Your vote for {$candidacy->position} has been securely cast!");
    }

    public function skipPosition(Request $request)
    {
        $request->validate([
            'position' => 'required|string',
        ]);

        $student = Auth::user();
        $activeSy = SchoolYear::where('is_active', 1)->first();

        if (!$activeSy) {
            return redirect()->route('student.voting');
        }

        $ballot = StudentBallot::where('user_id', $student->id)
            ->where('school_year', $activeSy->label)
            ->where('position', $request->position)
            ->first();

        if ($ballot) {
            $ballot->update(['submitted_at' => now()]);
            SscHelper::logActivity(
                $student->id,
                'STUDENT_VOTE_SKIP',
                "Skipped/Expired ballot for {$request->position}"
            );
        }

        return redirect()->route('student.voting');
    }

    public function results()
    {
        $activeSy = SchoolYear::where('is_active', 1)->first();

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
