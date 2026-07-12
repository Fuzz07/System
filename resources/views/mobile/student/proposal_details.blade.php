@php
    $pageTitle    = Str::limit($proposal->project_title, 30);
    $pageSubtitle = 'Project Details';
    $showBack     = true;
    $backUrl      = route('mobile.student.proposals');
    $contentClass = 'no-pad';
@endphp
@extends('layouts.mobile-student')

@section('content')

{{-- Hero --}}
<div class="detail-hero">
    <div class="d-flex gap-2 mb-3">
        @if($proposal->status === 'Approved')
        <span class="m-badge approved"><i class="bi bi-check-circle-fill"></i> Approved</span>
        @else
        <span class="m-badge pending"><i class="bi bi-clock"></i> Under Review</span>
        @endif
        @if($proposal->project_status === 'Completed')
        <span class="m-badge done"><i class="bi bi-patch-check-fill"></i> Completed</span>
        @endif
    </div>
    <div class="detail-hero-title">{{ $proposal->project_title }}</div>
    <div style="font-size:0.8rem;opacity:0.7;margin-top:8px;">
        <i class="bi bi-calendar3"></i> {{ $proposal->created_at?->format('M d, Y') }}
        &nbsp;·&nbsp;
        <i class="bi bi-hash"></i> SSC-PRP-{{ str_pad($proposal->id, 4, '0', STR_PAD_LEFT) }}
    </div>
</div>

<div style="padding:20px 16px; padding-bottom:160px;">

{{-- Info Strip --}}
<div class="detail-info-strip">
    <div class="detail-info-item">
        <div class="detail-info-label">Project Lead</div>
        <div class="detail-info-value" style="font-size:0.78rem;">{{ Str::limit($proposal->officer->fullname ?? 'N/A', 16) }}</div>
    </div>
    <div class="detail-info-item">
        <div class="detail-info-label">Status</div>
        <div class="detail-info-value" style="font-size:0.78rem;">{{ $proposal->project_status ?? 'Ongoing' }}</div>
    </div>
    <div class="detail-info-item">
        <div class="detail-info-label">Comments</div>
        <div class="detail-info-value">{{ count($comments) }}</div>
    </div>
</div>

{{-- Completion Proof --}}
@if($proposal->project_status === 'Completed' && $proposal->completion_proof)
<div class="proof-banner">
    <div>
        <div class="proof-label"><i class="bi bi-patch-check-fill"></i> Project Liquidated</div>
        <div class="proof-sub">Official receipt available for transparency.</div>
    </div>
    <a href="{{ asset('storage/' . $proposal->completion_proof) }}" target="_blank" class="proof-btn">
        <i class="bi bi-receipt"></i> Receipt
    </a>
</div>
@endif

{{-- Council Notes --}}
@if($proposal->admin_notes)
<div class="desc-section" style="background:rgba(245,166,35,0.06);border:1px solid rgba(245,166,35,0.2);margin-bottom:16px;">
    <div class="desc-section-title" style="color:#d97706;"><i class="bi bi-info-circle-fill"></i> Council Notes</div>
    <div class="desc-text" style="font-size:0.85rem;">{{ $proposal->admin_notes }}</div>
</div>
@endif

{{-- Description --}}
<div class="desc-section">
    <div class="desc-section-title">Project Description</div>
    <div class="desc-text">{!! nl2br(e($proposal->description)) !!}</div>
</div>

{{-- Print button --}}
<div style="text-align:center;margin:6px 0 20px;">
    <a href="{{ route('proposals.print', $proposal) }}" target="_blank"
       class="m-btn m-btn-secondary m-btn-sm">
        <i class="bi bi-printer"></i> Print Budget Proposal
    </a>
</div>

{{-- Transparency Pledge --}}
<div class="desc-section" style="background:rgba(79,70,229,0.04);border:1px solid rgba(79,70,229,0.1);text-align:center;">
    <i class="bi bi-shield-check" style="font-size:1.4rem;color:var(--primary);display:block;margin-bottom:6px;"></i>
    <div class="desc-section-title" style="color:var(--primary);">Transparency Pledge</div>
    <div class="desc-text" style="font-size:0.8rem;">
        @if($proposal->status === 'Approved')
            This project has been reviewed and approved by the SSC. All expenses will be published upon completion.
        @else
            This project is under evaluation. Your feedback helps the council assess its impact.
        @endif
    </div>
</div>

{{-- Comments Section --}}
<div class="section-header" style="margin-top:24px;">
    <div class="section-title"><i class="bi bi-chat-dots-fill" style="color:var(--primary);margin-right:5px;"></i>Discussion</div>
    <div class="section-title">{{ count($comments) }}</div>
</div>

<div class="comment-list" id="commentList">
    @forelse($comments as $c)
    <div class="comment-item">
        <div class="comment-avatar {{ $c->user->role === 'student' ? 'student-av' : 'officer-av' }}">
            @if($c->user->role === 'student')
                <i class="bi bi-person-fill"></i>
            @else
                {{ strtoupper(substr($c->user->fullname, 0, 1)) }}
            @endif
        </div>
        <div class="comment-body">
            <div class="comment-header">
                <span class="comment-author">
                    @if($c->user->role === 'student')
                        <i class="bi bi-person-fill-lock" style="font-size:0.7rem;"></i> Anonymous
                    @else
                        {{ $c->user->fullname }}
                        <span style="font-size:0.62rem;background:rgba(79,70,229,0.1);color:var(--primary);padding:1px 6px;border-radius:6px;margin-left:4px;">{{ ucfirst($c->user->role) }}</span>
                    @endif
                </span>
                <span class="comment-time">{{ $c->created_at?->diffForHumans() }}</span>
            </div>
            <div class="comment-text">{!! nl2br(e($c->comment)) !!}</div>
        </div>
    </div>
    @empty
    <div class="empty-state" style="padding:24px 0;">
        <i class="bi bi-chat-dots" style="font-size:2rem;"></i>
        <div class="empty-state-sub">No comments yet. Start the discussion!</div>
    </div>
    @endforelse
</div>

</div>{{-- /padding wrapper --}}
@endsection

{{-- Comment Input Bar --}}
@push('modals')
<div class="comment-input-wrap" id="commentInputWrap">
    <form method="POST" action="{{ route('mobile.student.proposal.comment', $proposal) }}"
          id="commentForm" style="display:flex;gap:10px;align-items:flex-end;width:100%;">
        @csrf
        <textarea name="comment" class="comment-input" id="commentInput"
                  placeholder="Share your thoughts…" rows="1" required></textarea>
        <button type="submit" class="comment-send-btn">
            <i class="bi bi-send-fill"></i>
        </button>
    </form>
</div>
@endpush

@push('scripts')
<script>
// Auto-resize textarea
const ci = document.getElementById('commentInput');
if (ci) {
    ci.addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 100) + 'px';
    });
}

// Scroll to bottom of comments on load
window.addEventListener('load', () => {
    const list = document.getElementById('commentList');
    if (list) list.scrollIntoView({ behavior: 'smooth', block: 'end' });
});
</script>
@endpush
