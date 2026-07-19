<?php

namespace App\Http\Controllers\Student;

use App\Helpers\SscHelper;
use App\Http\Controllers\Controller;
use App\Models\Candidacy;
use App\Models\SchoolYear;
use App\Models\Vote;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VotingController extends Controller
{
    public function index()
    {
        $student = Auth::user();
        $activeSy = $this->getActiveSchoolYear();

        if (!$activeSy) {
            return view('student.voting', [
                'activeSy' => null,
                'candidatesByPosition' => [],
                'myVotes' => [],
            ]);
        }

        return view('student.voting', [
            'activeSy' => $activeSy,
            'candidatesByPosition' => $this->getCandidatesByPosition($student, $activeSy),
            'myVotes' => $this->getMyVotes($student, $activeSy),
        ]);
    }

    public function store(Request $request)
    {
        $this->validateVoteRequest($request);

        $student = Auth::user();
        $activeSy = $this->getActiveSchoolYear();

        if (!$activeSy) {
            return back()->with('danger', 'No active school year set. Voting is unavailable.');
        }

        $candidacy = $this->findApprovedCandidacy($request->candidacy_id, $activeSy);
        if (!$candidacy) {
            return back()->with('danger', 'Invalid or unapproved candidate selected.');
        }

        if (!$this->authorizeRepresentativeVote($student, $candidacy)) {
            return back()->with('danger', 'You can only vote for the representative of your own department.');
        }

        if ($this->hasAlreadyVoted($student, $candidacy, $activeSy)) {
            return back()->with('danger', 'You have already cast a vote for this position.');
        }

        try {
            $this->recordVote($student, $candidacy, $activeSy);
        } catch (QueryException $exception) {
            return back()->with('danger', 'Your vote could not be recorded. It appears you have already voted for this position.');
        }

        return redirect()->route('student.voting')->with('success', 'Your vote has been cast successfully!');
    }

    public function indexMobile()
    {
        $student = Auth::user();
        $activeSy = $this->getActiveSchoolYear();

        if (!$activeSy) {
            return view('mobile.student.voting', [
                'activeSy' => null,
                'candidatesByPosition' => [],
                'myVotes' => [],
            ]);
        }

        return view('mobile.student.voting', [
            'activeSy' => $activeSy,
            'candidatesByPosition' => $this->getCandidatesByPosition($student, $activeSy),
            'myVotes' => $this->getMyVotes($student, $activeSy),
        ]);
    }

    public function storeMobile(Request $request)
    {
        $this->validateVoteRequest($request);

        $student = Auth::user();
        $activeSy = $this->getActiveSchoolYear();

        if (!$activeSy) {
            return back()->with('danger', 'No active school year set. Voting is unavailable.');
        }

        $candidacy = $this->findApprovedCandidacy($request->candidacy_id, $activeSy);
        if (!$candidacy) {
            return back()->with('danger', 'Invalid or unapproved candidate selected.');
        }

        if (!$this->authorizeRepresentativeVote($student, $candidacy)) {
            return back()->with('danger', 'You can only vote for the representative of your own department.');
        }

        if ($this->hasAlreadyVoted($student, $candidacy, $activeSy)) {
            return back()->with('danger', 'You have already cast a vote for this position.');
        }

        try {
            $this->recordVote($student, $candidacy, $activeSy, true);
        } catch (QueryException $exception) {
            return back()->with('danger', 'Your vote could not be recorded. It appears you have already voted for this position.');
        }

        return redirect()->route('mobile.student.voting')->with('success', 'Your vote has been cast successfully!');
    }

    private function getActiveSchoolYear()
    {
        return SchoolYear::where('is_active', 1)->first();
    }

    private function getCandidatesByPosition($student, $activeSy)
    {
        $candidates = Candidacy::with('user')
            ->where('school_year', $activeSy->label)
            ->where('status', 'approved')
            ->get();

        $grouped = [];
        foreach ($candidates as $c) {
            $pos = $c->position;
            if (str_ends_with($pos, ' Representative')) {
                $expectedPos = $student->department . ' Representative';
                if (strcasecmp($pos, $expectedPos) !== 0) {
                    continue;
                }
            }
            $grouped[$pos][] = $c;
        }

        return $grouped;
    }

    private function getMyVotes($student, $activeSy)
    {
        return Vote::where('user_id', $student->id)
            ->where('school_year', $activeSy->label)
            ->get()
            ->keyBy('position');
    }

    private function validateVoteRequest(Request $request)
    {
        $request->validate([
            'candidacy_id' => 'required|integer|exists:candidacies,id',
        ]);
    }

    private function findApprovedCandidacy(int $candidacyId, $activeSy)
    {
        return Candidacy::where('id', $candidacyId)
            ->where('school_year', $activeSy->label)
            ->where('status', 'approved')
            ->first();
    }

    private function authorizeRepresentativeVote($student, $candidacy): bool
    {
        if (!str_ends_with($candidacy->position, ' Representative')) {
            return true;
        }

        $expectedPos = $student->department . ' Representative';
        return strcasecmp($candidacy->position, $expectedPos) === 0;
    }

    private function hasAlreadyVoted($student, $candidacy, $activeSy): bool
    {
        return Vote::where('user_id', $student->id)
            ->where('position', $candidacy->position)
            ->where('school_year', $activeSy->label)
            ->exists();
    }

    private function recordVote($student, $candidacy, $activeSy, bool $mobile = false)
    {
        Vote::create([
            'user_id' => $student->id,
            'candidacy_id' => $candidacy->id,
            'position' => $candidacy->position,
            'school_year' => $activeSy->label,
        ]);

        SscHelper::logActivity(
            $student->id,
            'STUDENT_VOTE',
            sprintf(
                'Cast a %s vote for %s as %s',
                $mobile ? 'mobile' : 'web',
                $candidacy->user->fullname,
                $candidacy->position
            )
        );
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

    public function resultsMobile()
    {
        $activeSy = $this->getActiveSchoolYear();

        if (!$activeSy) {
            return view('mobile.student.election-results', [
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

        return view('mobile.student.election-results', compact('activeSy', 'candidatesByPosition'));
    }
}
