<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\SscHelper;
use App\Http\Controllers\Controller;
use App\Models\Candidacy;
use App\Models\SchoolYear;
use App\Models\User;
use App\Notifications\ElectionOpenNotification;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CandidacyController extends Controller
{
    public function index()
    {
        $candidacies = Candidacy::with('user')
            ->orderByDesc('id')
            ->get();

        $stats = [
            'total' => $candidacies->count(),
            'pending' => $candidacies->where('status', 'pending')->count(),
            'approved' => $candidacies->where('status', 'approved')->count(),
            'rejected' => $candidacies->where('status', 'rejected')->count(),
        ];

        $activeSy = SchoolYear::where('is_active', 1)->first();

        return view('admin.candidacies', compact('candidacies', 'stats', 'activeSy'));
    }

    public function openVoting(Request $request)
    {
        $activeSy = SchoolYear::where('is_active', 1)->first();
        if (!$activeSy) {
            return back()->with('danger', 'No active school year set.');
        }

        $startsAt = now();
        $endsAt = now()->addHours(8);

        $activeSy->update([
            'candidacy_open' => false,
            'voting_open' => true,
            'voting_starts_at' => $startsAt,
            'voting_ends_at' => $endsAt,
            'results_announced' => false,
        ]);

        // Post an official announcement on the board
        \App\Models\Announcement::create([
            'title' => 'Supreme Student Council Elections are OPEN!',
            'content' => "Supreme Student Council voting has officially commenced! The election is open for exactly 8 hours starting from {$startsAt->format('h:i A')} and will close at {$endsAt->format('h:i A')} today. Every active student can vote once per position. Note: You have a 1-minute time limit per position once you start voting on it. Make your voice heard!",
            'created_by' => Auth::id()
        ]);

        // Fetch active students and notify them
        $students = User::where('role', 'student')->where('status', 'active')->get();
        foreach ($students as $student) {
            try {
                $student->notify(new ElectionOpenNotification($startsAt, $endsAt));
            } catch (\Exception $e) {
                // Ignore notification mailer errors if any
            }
        }

        // Send Firebase Push Notification
        $studentIds = $students->pluck('id')->toArray();
        if (!empty($studentIds)) {
            PushNotificationService::sendToUsers(
                $studentIds,
                'SSC Elections are OPEN!',
                "Cast your votes between {$startsAt->format('h:i A')} and {$endsAt->format('h:i A')}. 1-minute limit per position!"
            );
        }

        SscHelper::logActivity(
            Auth::id(),
            'ELECTION_VOTING_OPEN',
            "Opened voting for SY {$activeSy->label} from {$startsAt} to {$endsAt}"
        );

        return back()->with('success', 'Elections are now officially open for the next 8 hours! Notifications sent to all students.');
    }

    public function closeVoting(Request $request)
    {
        $activeSy = SchoolYear::where('is_active', 1)->first();
        if (!$activeSy) {
            return back()->with('danger', 'No active school year set.');
        }

        $activeSy->update([
            'voting_open' => false,
        ]);

        SscHelper::logActivity(
            Auth::id(),
            'ELECTION_VOTING_CLOSE',
            "Voting manually closed for SY {$activeSy->label}"
        );

        return back()->with('warning', 'Elections voting period has been manually closed.');
    }

    public function announceResults(Request $request)
    {
        $activeSy = SchoolYear::where('is_active', 1)->first();
        if (!$activeSy) {
            return back()->with('danger', 'No active school year set.');
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

        // De-promote current officers/treasurers back to students
        User::whereIn('role', ['officer', 'treasurer'])
            ->update([
                'role' => 'student',
                'position' => null,
                'party' => null,
            ]);

        $winnersText = [];
        foreach ($candidatesByPosition as $position => $cands) {
            if (empty($cands)) {
                continue;
            }

            $winner = $cands[0];
            $winnerUser = $winner->user;

            $newRole = (strcasecmp($position, 'SSC Treasurer') === 0) ? 'treasurer' : 'officer';

            $winnerUser->update([
                'role' => $newRole,
                'position' => $position,
                'status' => 'active',
            ]);

            $winnersText[] = "- **{$position}**: {$winnerUser->fullname} (Winner, {$winner->votes_count} votes)";
        }

        $activeSy->update([
            'voting_open' => false,
            'results_announced' => true,
        ]);

        $winnersBody = !empty($winnersText) ? implode("\n", $winnersText) : "No candidates filed for any positions.";
        
        \App\Models\Announcement::create([
            'title' => "Official Election Results - SY {$activeSy->label}",
            'content' => "The Supreme Student Council elections have officially concluded! Here are the official winners who will lead our student body:\n\n" . $winnersBody . "\n\nCongratulations to our new student leaders! Active officers list and access credentials have been updated accordingly.",
            'created_by' => Auth::id()
        ]);

        SscHelper::logActivity(
            Auth::id(),
            'ELECTION_ANNOUNCED',
            "Announced results and updated officer roster for SY {$activeSy->label}"
        );

        return back()->with('success', 'Elections finalized! Official results have been announced, and the new officers have been promoted.');
    }

    public function destroy(Candidacy $candidacy)
    {
        $name = $candidacy->user->fullname;
        $candidacy->delete();

        SscHelper::logActivity(
            Auth::id(),
            'ADMIN_DELETE_CANDIDACY',
            "Deleted candidacy filing for {$name}"
        );

        return redirect()->route('admin.candidacies')->with('success', "Candidacy application for {$name} deleted successfully.");
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
