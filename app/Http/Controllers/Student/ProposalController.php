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

    public function reply(Request $request, Proposal $proposal, ProposalComment $comment)
    {
        $this->authorizeStudentProposalAccess($proposal);

        $request->validate(['comment' => 'required|string|min:1|max:2000']);
        ProposalComment::create([
            'proposal_id' => $proposal->id,
            'user_id'     => Auth::id(),
            'comment'     => $request->comment,
            // Threads are one level deep: replying to a reply joins its thread.
            'parent_id'   => $comment->parent_id ?? $comment->id,
        ]);

        return redirect()->back()->with('success', 'Reply posted!');
    }

    public function updateComment(Request $request, Proposal $proposal, ProposalComment $comment)
    {
        $this->authorizeStudentProposalAccess($proposal);
        // Students may reword or remove their own comments, never anyone else's.
        abort_unless((int) $comment->user_id === (int) Auth::id(), 403);

        $request->validate(['comment' => 'required|string|min:1|max:2000']);
        $comment->update(['comment' => $request->comment]);

        return redirect()->back()->with('success', 'Comment updated.');
    }

    public function destroyComment(Proposal $proposal, ProposalComment $comment)
    {
        $this->authorizeStudentProposalAccess($proposal);
        abort_unless((int) $comment->user_id === (int) Auth::id(), 403);

        $comment->delete();

        return redirect()->back()->with('success', 'Comment deleted.');
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

        $sscExecutiveOfficers = config('ssc.executive_officers', []);

        // The print page normally opens in a new tab, where browser history has
        // no previous entry. Give its back button a real portal-specific URL.
        $backUrl = match ($user->role) {
            'admin' => route('admin.proposals'),
            'officer', 'treasurer' => route('officer.proposals'),
            default => route('student.proposal.show', $proposal),
        };

        return view('student.print', compact('proposal', 'sscExecutiveOfficers', 'backUrl'));
    }

    private function authorizeStudentProposalAccess(Proposal $proposal): void
    {
        if (!in_array($proposal->status, self::STUDENT_VISIBLE_STATUSES, true)) {
            abort(404);
        }
    }
}
