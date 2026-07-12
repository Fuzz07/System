@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-student') @endsection

@section('content')
<div class="page-header"><div><h1>Feedback &amp; Concerns</h1><p>Submit your questions or concerns to the SSC</p></div></div>

<div class="row g-3">
    <div class="col-md-5">
        <div class="card"><div class="card-header-custom"><span class="card-title">Submit New Feedback</span></div>
        <div class="card-body-custom">
            <form method="POST" action="{{ route('student.feedback.store') }}">@csrf
                <div class="mb-3"><label class="form-label-custom">Your Message</label><textarea name="message" class="form-control-custom" rows="6" placeholder="Type your question, concern, or suggestion..." required style="resize:vertical;"></textarea></div>
                <button type="submit" class="btn-primary-custom w-100 justify-content-center"><i class="bi bi-send"></i> Submit Feedback</button>
            </form>
            <div class="mt-3 p-3 rounded-3" style="background:#f0f9f6;border:1px solid #c6f0e3;font-size:.8rem;color:#2d7a5e;"><i class="bi bi-info-circle-fill"></i> <strong>Note:</strong> We respond within 3–5 working days. Your identity remains confidential.</div>
        </div></div>
    </div>
    <div class="col-md-7">
        <div class="card"><div class="card-header-custom"><span class="card-title">My Submitted Feedback</span><span class="badge bg-secondary">{{ count($feedbacks) }}</span></div>
        @forelse($feedbacks as $fb)
        <div class="px-4 py-3 border-bottom">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div style="font-size:.8rem;color:#718096;"><i class="bi bi-clock"></i> {{ $fb->created_at?->format('M d, Y') }}</div>
                {!! \App\Helpers\SscHelper::statusBadge($fb->status) !!}
            </div>
            <div style="font-size:.875rem;color:#2d3748;margin-bottom:8px;">{!! nl2br(e($fb->message)) !!}</div>
            @if($fb->reply)
            <div class="p-3 rounded-3" style="background:#f0f9f6;border-left:3px solid var(--accent);">
                <div style="font-size:.72rem;font-weight:700;color:var(--accent);margin-bottom:4px;"><i class="bi bi-reply-fill"></i> Reply from {{ $fb->replier->fullname ?? 'SSC Admin' }}</div>
                <div style="font-size:.82rem;color:#2d3748;">{!! nl2br(e($fb->reply)) !!}</div>
            </div>
            @endif
        </div>
        @empty
        <div class="text-center py-5 text-muted"><i class="bi bi-chat-dots" style="font-size:2rem;opacity:.3;"></i><div class="mt-2" style="font-size:.85rem;">No feedback submitted yet.</div></div>
        @endforelse
        </div>
    </div>
</div>
@endsection

@section('chatbot') @include('partials.chatbot') @endsection
