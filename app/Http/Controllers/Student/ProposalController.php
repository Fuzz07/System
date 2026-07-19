<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Proposal;
use App\Models\ProposalComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProposalController extends Controller
{
    private const STUDENT_VISIBLE_STATUSES = ['Approved', 'Pending'];

    public function index()
    {
        $proposals = Proposal::with('officer')
            ->withCount('comments')
            ->whereIn('status', self::STUDENT_VISIBLE_STATUSES)
            ->orderByRaw("FIELD(status, 'Pending', 'Approved')")
            ->orderByDesc('created_at')
            ->get();
        return view('student.proposals', compact('proposals'));
    }

    public function show(Proposal $proposal)
    {
        $this->authorizeStudentProposalAccess($proposal);

        $proposal->load('officer');
        $comments = ProposalComment::with('user')
            ->where('proposal_id', $proposal->id)
            ->orderByDesc('created_at')
            ->get();
        return view('student.proposal_details', compact('proposal', 'comments'));
    }

    public function comment(Request $request, Proposal $proposal)
    {
        $this->authorizeStudentProposalAccess($proposal);

        $request->validate(['comment' => 'required|string|min:1|max:2000']);
        ProposalComment::create([
            'proposal_id' => $proposal->id,
            'user_id'     => Auth::id(),
            'comment'     => $request->comment,
        ]);
        return redirect()->route('student.proposal.show', $proposal)->with('success', 'Comment added!');
    }

    public function print(Proposal $proposal)
    {
        $user = Auth::user();

        if ($user->role === 'officer' && (int) $proposal->officer_id !== (int) $user->id) {
            abort(403, 'You can only print your own proposals.');
        }

        if ($user->role === 'student') {
            $this->authorizeStudentProposalAccess($proposal);
        }

        $proposal->load('officer');

        $sscExecutiveOfficers = [
            ['position' => 'President', 'name' => 'Villacarlos, Jireh Joy A.', 'party' => 'ABANTE PARTY', 'icon' => 'bi-award'],
            ['position' => 'Vice President', 'name' => 'Licardo, Juvy Irish C.', 'party' => 'ABANTE PARTY', 'icon' => 'bi-person-check'],
            ['position' => 'Secretary', 'name' => 'Carabio, Margarette B.', 'party' => 'ABANTE PARTY', 'icon' => 'bi-journal-text'],
            ['position' => 'Treasurer', 'name' => 'Maru, Florane D.', 'party' => 'ABANTE PARTY', 'icon' => 'bi-safe2'],
            ['position' => 'Auditor', 'name' => 'Salvana, Althea Mae D.', 'party' => 'ABANTE PARTY', 'icon' => 'bi-clipboard-check'],
            ['position' => 'PIO', 'name' => 'Manos, Shanei M.', 'party' => 'ABANTE PARTY', 'icon' => 'bi-megaphone'],
            ['position' => 'PIO', 'name' => 'Escala, Marlon', 'party' => 'ABANTE PARTY', 'icon' => 'bi-megaphone'],
        ];

        return view('student.print', compact('proposal', 'sscExecutiveOfficers'));
    }

    private function authorizeStudentProposalAccess(Proposal $proposal): void
    {
        if (!in_array($proposal->status, self::STUDENT_VISIBLE_STATUSES, true)) {
            abort(404);
        }
    }
}