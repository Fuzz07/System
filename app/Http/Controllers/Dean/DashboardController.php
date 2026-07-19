<?php

namespace App\Http\Controllers\Dean;

use App\Helpers\SscHelper;
use App\Http\Controllers\Controller;
use App\Models\Candidacy;
use App\Models\SchoolYear;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $dean = Auth::user();
        $activeSy = SchoolYear::where('is_active', 1)->first();
        $dept = $dean->department;

        $query = Candidacy::with('user')->where('school_year', $activeSy->label ?? '');

        if ($dept === 'BSED/BEED') {
            $query->whereIn('department', ['BSED', 'BEED', 'BSED/BEED']);
        } else {
            $query->where('department', $dept);
        }

        $candidacies = $query->orderByDesc('id')->get();

        $stats = [
            'total' => $candidacies->count(),
            'pending' => $candidacies->where('status', 'pending')->count(),
            'approved' => $candidacies->where('status', 'approved')->count(),
            'rejected' => $candidacies->where('status', 'rejected')->count(),
        ];

        return view('dean.dashboard', compact('candidacies', 'stats', 'activeSy'));
    }

    public function vote(Candidacy $candidacy)
    {
        $this->authorizeDeanAccess($candidacy);

        $candidacy->update(['status' => 'approved']);

        SscHelper::logActivity(
            Auth::id(),
            'DEAN_VOTE',
            "Voted/Endorsed student {$candidacy->user->fullname} to run as {$candidacy->position}"
        );

        return redirect()->route('dean.dashboard')->with('success', "Candidacy for {$candidacy->user->fullname} has been approved/selected to run.");
    }

    public function reject(Candidacy $candidacy)
    {
        $this->authorizeDeanAccess($candidacy);

        $candidacy->update(['status' => 'rejected']);

        SscHelper::logActivity(
            Auth::id(),
            'DEAN_REJECT',
            "Declined candidacy for student {$candidacy->user->fullname}"
        );

        return redirect()->route('dean.dashboard')->with('warning', "Candidacy for {$candidacy->user->fullname} has been declined.");
    }

    protected function authorizeDeanAccess(Candidacy $candidacy)
    {
        $activeSy = SchoolYear::where('is_active', 1)->first();
        if (!$activeSy || $candidacy->school_year !== $activeSy->label || $candidacy->status !== 'pending') {
            abort(404);
        }

        $dean = Auth::user();
        $dept = $dean->department;
        $candDept = $candidacy->department;

        $authorized = false;
        if ($dept === 'BSED/BEED') {
            $authorized = in_array($candDept, ['BSED', 'BEED', 'BSED/BEED'], true);
        } else {
            $authorized = ($dept === $candDept);
        }

        if (!$authorized) {
            abort(403, 'Unauthorized department access.');
        }
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