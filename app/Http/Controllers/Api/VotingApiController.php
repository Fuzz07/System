<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Candidacy;
use App\Models\SchoolYear;
use App\Models\StudentBallot;
use App\Models\Vote;
use App\Helpers\SscHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VotingApiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('role:student');
    }

    /**
     * Get election status, relevant positions, and student ballot progress.
     */
    public function status()
    {
        $student = auth('api')->user();
        $activeSy = SchoolYear::where('is_active', 1)->first();

        if (! $activeSy || ! $activeSy->voting_open) {
            return response()->json([
                'success' => true,
                'data'    => [
                    'voting_open' => false,
                    'message'     => 'Voting is currently closed.',
                    'school_year' => $activeSy?->label,
                ],
            ]);
        }

        $repPosition = $student->department . ' Representative';
        $allPositions = [
            'SSC President',
            'SSC Vice President',
            'SSC Secretary',
            'SSC Treasurer',
            $repPosition,
        ];

        // Positions that have approved candidates
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

        $ballotStatus = [];
        foreach ($positionsWithCandidates as $pos) {
            $ballot = $ballots->get($pos);
            $hasVoted = Vote::where('user_id', $student->id)
                ->where('position', $pos)
                ->where('school_year', $activeSy->label)
                ->exists();

            $ballotStatus[$pos] = [
                'has_started'      => (bool) $ballot,
                'has_voted'        => $hasVoted,
                'is_submitted'     => (bool) $ballot?->submitted_at,
                'started_at'       => $ballot?->started_at?->toIso8601String(),
                'seconds_remaining'=> $ballot && ! $ballot->submitted_at 
                    ? max(0, 60 - (now()->timestamp - $ballot->started_at->timestamp))
                    : 0,
            ];
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'voting_open'             => true,
                'school_year'             => $activeSy->label,
                'positions'               => $positionsWithCandidates,
                'ballot_status'           => $ballotStatus,
            ],
        ]);
    }

    /**
     * Get approved candidates for active election.
     */
    public function candidates(Request $request)
    {
        $student = auth('api')->user();
        $activeSy = SchoolYear::where('is_active', 1)->first();

        if (! $activeSy) {
            return response()->json([
                'success' => false,
                'message' => 'No active school year found.',
            ], 404);
        }

        $repPosition = $student->department . ' Representative';
        $allowedPositions = [
            'SSC President',
            'SSC Vice President',
            'SSC Secretary',
            'SSC Treasurer',
            $repPosition,
        ];

        $query = Candidacy::with('user:id,fullname,email,department,year_level,profile_pic')
            ->where('school_year', $activeSy->label)
            ->where('status', 'approved')
            ->whereIn('position', $allowedPositions);

        if ($request->filled('position')) {
            $query->where('position', $request->position);
        }

        $candidates = $query->get()->map(function ($c) {
            return [
                'id'          => $c->id,
                'position'    => $c->position,
                'party'       => $c->party,
                'platform'    => $c->platform,
                'photo_url'   => $c->photo_url ?? $c->user?->photo_url,
                'student'     => [
                    'id'         => $c->user?->id,
                    'fullname'   => $c->user?->fullname,
                    'department' => $c->user?->department,
                    'year_level' => $c->user?->year_level,
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => [
                'school_year' => $activeSy->label,
                'candidates'  => $candidates,
            ],
        ]);
    }

    /**
     * Start a ballot countdown (1 minute) for a specific position.
     */
    public function startBallot(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'position' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $position = trim($validated['position']);

        $student = auth('api')->user();
        $activeSy = SchoolYear::where('is_active', 1)->first();

        if (! $activeSy || ! $activeSy->voting_open) {
            return response()->json([
                'success' => false,
                'message' => 'Voting period is not active.',
            ], 403);
        }

        $existsCandidate = Candidacy::where('school_year', $activeSy->label)
            ->where('status', 'approved')
            ->where('position', $position)
            ->exists();

        if (! $existsCandidate) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or ineligible election position.',
            ], 422);
        }

        $exists = StudentBallot::where('user_id', $student->id)
            ->where('school_year', $activeSy->label)
            ->where('position', $position)
            ->first();

        if ($exists) {
            if ($exists->submitted_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already completed the ballot for this position.',
                ], 400);
            }

            $elapsed = now()->timestamp - $exists->started_at->timestamp;
            if ($elapsed >= 60) {
                $exists->update(['submitted_at' => now()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Ballot time limit has expired for this position.',
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Ballot already active.',
                'data'    => [
                    'position'          => $position,
                    'seconds_remaining' => 60 - $elapsed,
                ],
            ]);
        }

        $ballot = new StudentBallot();
        $ballot->user_id     = $student->id;
        $ballot->position    = $position;
        $ballot->school_year = $activeSy->label;
        $ballot->started_at  = now();
        $ballot->ip_address  = $request->ip();
        $ballot->user_agent  = $request->userAgent();
        $ballot->save();

        SscHelper::logActivity(
            $student->id,
            'API_VOTING_TIMER_START',
            "Started voting countdown for {$position}"
        );

        return response()->json([
            'success' => true,
            'message' => 'Ballot timer started. You have 60 seconds to cast your vote.',
            'data'    => [
                'position'          => $position,
                'seconds_remaining' => 60,
                'started_at'        => $ballot->started_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Cast vote for candidate.
     */
    public function castVote(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'candidacy_id' => 'required|integer|exists:candidacies,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $candidacyId = (int) $validated['candidacy_id'];

        $student = auth('api')->user();
        $activeSy = SchoolYear::where('is_active', 1)->first();

        if (! $activeSy || ! $activeSy->voting_open) {
            return response()->json([
                'success' => false,
                'message' => 'Voting period is not active.',
            ], 403);
        }

        $candidacy = Candidacy::where('id', $candidacyId)
            ->where('school_year', $activeSy->label)
            ->where('status', 'approved')
            ->first();

        if (! $candidacy) {
            return response()->json([
                'success' => false,
                'message' => 'Candidate is not eligible or not found for this school year.',
            ], 404);
        }

        $ballot = StudentBallot::where('user_id', $student->id)
            ->where('school_year', $activeSy->label)
            ->where('position', $candidacy->position)
            ->first();

        if (! $ballot) {
            return response()->json([
                'success' => false,
                'message' => 'Ballot session was not initialized. Call /start-ballot first.',
            ], 400);
        }

        if ($ballot->submitted_at) {
            return response()->json([
                'success' => false,
                'message' => 'You have already submitted a ballot for this position.',
            ], 400);
        }

        $elapsed = now()->timestamp - $ballot->started_at->timestamp;
        if ($elapsed >= 60) {
            $ballot->update(['submitted_at' => now()]);
            return response()->json([
                'success' => false,
                'message' => 'Voting time limit (60s) has expired.',
            ], 400);
        }

        // Verify duplicate votes
        $voteExists = Vote::where('user_id', $student->id)
            ->where('position', $candidacy->position)
            ->where('school_year', $activeSy->label)
            ->exists();

        if ($voteExists) {
            $ballot->update(['submitted_at' => now()]);
            return response()->json([
                'success' => false,
                'message' => 'You have already voted for this position.',
            ], 400);
        }

        Vote::create([
            'user_id'      => $student->id,
            'candidacy_id' => $candidacy->id,
            'position'     => $candidacy->position,
            'school_year'  => $activeSy->label,
            'ip_address'   => $request->ip(),
            'user_agent'   => $request->userAgent(),
        ]);

        $ballot->update(['submitted_at' => now()]);

        SscHelper::logActivity(
            $student->id,
            'API_VOTE_CAST',
            "Cast vote for {$candidacy->position} via API"
        );

        return response()->json([
            'success' => true,
            'message' => "Your vote for {$candidacy->position} was successfully cast!",
        ]);
    }

    /**
     * Get votes cast by the authenticated student for active election.
     */
    public function myVotes()
    {
        $student = auth('api')->user();
        $activeSy = SchoolYear::where('is_active', 1)->first();

        if (! $activeSy) {
            return response()->json([
                'success' => true,
                'data'    => [],
            ]);
        }

        $votes = Vote::with(['candidacy.user:id,fullname,profile_pic'])
            ->where('user_id', $student->id)
            ->where('school_year', $activeSy->label)
            ->get()
            ->map(function ($v) {
                return [
                    'id'          => $v->id,
                    'position'    => $v->position,
                    'candidate'   => $v->candidacy?->user?->fullname,
                    'photo_url'   => $v->candidacy?->photo_url ?? $v->candidacy?->user?->photo_url,
                    'party'       => $v->candidacy?->party,
                    'voted_at'    => $v->created_at?->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => [
                'school_year' => $activeSy->label,
                'votes'       => $votes,
            ],
        ]);
    }
}
