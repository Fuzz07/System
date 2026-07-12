<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\SscHelper;
use App\Http\Controllers\Controller;
use App\Models\Candidacy;
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

        return view('admin.candidacies', compact('candidacies', 'stats'));
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
