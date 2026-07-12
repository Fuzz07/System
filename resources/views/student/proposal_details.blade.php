@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-student') @endsection

@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
            <div class="p-4 p-md-5">
                <div class="mb-4">
                    <a href="{{ route('student.proposals') }}" class="text-decoration-none text-muted small mb-3 d-inline-block"><i class="bi bi-arrow-left"></i> Back to All Projects</a>
                    <h1 class="h2 fw-bold text-dark mb-3">{{ $proposal->project_title }}</h1>
                    <div class="d-flex flex-wrap gap-3 align-items-center">
                        @if($proposal->status === 'Approved')<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2">Approved Initiative</span>
                        @else <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-2">Pending Admin Review</span>@endif
                        <div class="text-muted small"><i class="bi bi-calendar3"></i> {{ $proposal->created_at?->format('M d, Y') }}</div>
                    </div>
                </div>
                <div class="mb-5"><h5 class="fw-bold mb-3">Project Description</h5><div class="text-muted" style="line-height:1.8;font-size:1.05rem;">{!! nl2br(e($proposal->description)) !!}</div></div>
                @if($proposal->admin_notes)<div class="p-4 rounded-4 mb-4" style="background:rgba(245,166,35,.1);border:1px solid rgba(245,166,35,.2);"><h6 class="fw-bold text-warning-emphasis mb-2"><i class="bi bi-info-circle-fill"></i> Council Notes</h6><div class="text-dark small opacity-75">{{ $proposal->admin_notes }}</div></div>@endif
                @if($proposal->project_status === 'Completed' && $proposal->completion_proof)<div class="p-4 rounded-4 bg-light border d-flex justify-content-between align-items-center mb-5"><div><h6 class="fw-bold text-success mb-1"><i class="bi bi-patch-check-fill"></i> Project Liquidated</h6><div class="text-muted small">Official receipt available.</div></div><a href="{{ asset('storage/' . $proposal->completion_proof) }}" target="_blank" class="btn btn-sm btn-success px-4" style="border-radius:10px;"><i class="bi bi-receipt"></i> View Receipt</a></div>@endif
            </div>
        </div>

        {{-- Comments --}}
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
            <div class="card-header-custom bg-white border-bottom p-4"><h5 class="mb-0 fw-bold"><i class="bi bi-chat-dots"></i> Student Discussion ({{ count($comments) }})</h5></div>
            <div class="p-4">
                <form method="POST" action="{{ route('student.proposal.comment', $proposal) }}" class="mb-5">@csrf
                    <div class="mb-3"><textarea name="comment" class="form-control-custom" rows="3" placeholder="Share your thoughts..." required style="border-radius:15px;"></textarea></div>
                    <button type="submit" class="btn-primary-custom px-4">Post Comment <i class="bi bi-send ms-2"></i></button>
                </form>

                <div class="comment-list d-flex flex-column gap-4">
                    @forelse($comments as $c)
                    <div class="d-flex gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0 {{ $c->user->role === 'student' ? 'bg-light text-muted' : 'bg-primary bg-opacity-10 text-primary' }}" style="width:48px;height:48px;font-weight:700;">
                            @if($c->user->role === 'student')<i class="bi bi-person-circle"></i>@else {{ strtoupper(substr($c->user->fullname, 0, 1)) }}@endif
                        </div>
                        <div class="flex-fill">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div>
                                    @if($c->user->role === 'student')<span class="fw-bold text-dark small"><i class="bi bi-person-fill-lock"></i> Anonymous Student</span>
                                    @else <span class="fw-bold text-dark small">{{ $c->user->fullname }}</span> <span class="badge bg-primary bg-opacity-10 text-primary small ms-1" style="font-size:0.6rem;">{{ ucfirst($c->user->role) }}</span>@endif
                                </div>
                                <span class="text-muted small" style="font-size:0.7rem;">{{ $c->created_at?->diffForHumans() }}</span>
                            </div>
                            <div class="p-3 bg-light rounded-4 small text-dark" style="line-height:1.6;">{!! nl2br(e($c->comment)) !!}</div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4 text-muted small">No comments yet. Be the first to start the discussion!</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden sticky-top" style="top:2rem;">
            <div class="p-4">
                <h6 class="fw-bold mb-4">Project Information</h6>
                <div class="mb-4"><label class="text-muted small text-uppercase fw-bold mb-1" style="letter-spacing:0.05em;">Project Lead</label>
                    <div class="d-flex align-items-center gap-3"><div class="avatar-sm bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center rounded-circle" style="width:40px;height:40px;font-weight:700;">{{ strtoupper(substr($proposal->officer->fullname ?? 'N', 0, 1)) }}</div><div class="fw-bold small">{{ $proposal->officer->fullname ?? 'N/A' }}</div></div>
                </div>
                <div class="mb-4"><label class="text-muted small text-uppercase fw-bold mb-1">Project ID</label><div class="fw-bold small text-dark">#SSC-PRP-{{ str_pad($proposal->id, 4, '0', STR_PAD_LEFT) }}</div></div>
                <div class="mb-4">
                    <a href="{{ route('proposals.print', $proposal) }}" target="_blank" class="btn btn-outline-secondary btn-sm w-100" style="border-radius:10px; font-weight: 600;">
                        <i class="bi bi-printer"></i> Print Budget Proposal
                    </a>
                </div>
                <div class="mb-0"><label class="text-muted small text-uppercase fw-bold mb-1">Transparency Pledge</label>
                    <p class="text-muted mb-0" style="font-size:0.75rem;line-height:1.6;">
                        @if($proposal->status === 'Approved') This project has been reviewed and approved by the SSC. All expenses will be published upon completion.
                        @else This project is under evaluation. Your feedback helps the council assess its impact. @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('chatbot') @include('partials.chatbot') @endsection
