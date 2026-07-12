@php
    $pageTitle    = 'Feedback';
    $pageSubtitle = 'Your Concerns Matter';
@endphp
@extends('layouts.mobile-student')

@section('content')

{{-- Compose Card --}}
<div class="feedback-compose">
    <div style="font-size:0.78rem;font-weight:700;color:var(--slate-500);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:10px;">
        <i class="bi bi-pencil-square" style="color:var(--primary);"></i> New Message
    </div>
    <form method="POST" action="{{ route('mobile.student.feedback.store') }}" id="feedbackForm">
        @csrf
        <textarea name="message"
                  class="w-100"
                  id="feedbackInput"
                  rows="3"
                  placeholder="Type your question, concern, or suggestion…"
                  required
                  style="
                    width:100%;border:1px solid var(--slate-200);border-radius:14px;
                    padding:12px 14px;font-size:0.9rem;font-family:inherit;
                    color:var(--slate-900);background:var(--slate-50);resize:none;
                    transition:var(--transition);min-height:80px;
                  "></textarea>
        <button type="submit" class="m-btn m-btn-primary m-btn-block" style="margin-top:10px;">
            <i class="bi bi-send-fill"></i> Send Message
        </button>
    </form>
    <div style="margin-top:10px;padding:10px 12px;background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.2);border-radius:10px;font-size:0.75rem;color:#059669;display:flex;align-items:center;gap:8px;">
        <i class="bi bi-info-circle-fill"></i>
        <span>We respond within 3–5 working days. Your identity remains confidential.</span>
    </div>
</div>

{{-- Thread Section --}}
<div class="section-header">
    <div class="section-title"><i class="bi bi-chat-left-dots-fill" style="color:var(--primary);margin-right:5px;"></i>My Messages</div>
    <span style="font-size:0.75rem;background:var(--slate-200);color:var(--slate-600);padding:2px 10px;border-radius:20px;font-weight:600;">{{ count($feedbacks) }}</span>
</div>

@forelse($feedbacks as $fb)
<div class="feedback-thread" style="margin-bottom:20px;">
    {{-- Student message --}}
    <div class="feedback-bubble">
        <div class="bubble-avatar student"><i class="bi bi-person-fill"></i></div>
        <div class="bubble-content">
            <div class="bubble-meta">
                <i class="bi bi-person-fill-lock"></i> You &nbsp;·&nbsp;
                {{ $fb->created_at?->format('M d, Y') }}
                &nbsp;·&nbsp;
                {!! \App\Helpers\SscHelper::statusBadge($fb->status) !!}
            </div>
            <div class="bubble-text sent">{!! nl2br(e($fb->message)) !!}</div>
        </div>
    </div>

    {{-- Reply bubble --}}
    @if($fb->reply)
    <div class="feedback-bubble" style="flex-direction:row-reverse;">
        <div class="bubble-avatar admin"><i class="bi bi-shield-check"></i></div>
        <div class="bubble-content" style="text-align:right;">
            <div class="bubble-meta" style="text-align:right;">
                {{ $fb->replier->fullname ?? 'SSC Admin' }} &nbsp;·&nbsp; SSC
            </div>
            <div class="bubble-text received" style="display:inline-block;text-align:left;">{!! nl2br(e($fb->reply)) !!}</div>
        </div>
    </div>
    @else
    <div style="text-align:center;font-size:0.72rem;color:var(--slate-400);padding:4px 0;">
        <i class="bi bi-clock"></i> Awaiting reply…
    </div>
    @endif
</div>

@if(!$loop->last)
<hr style="border-color:var(--slate-100);margin:4px 0 20px;">
@endif

@empty
<div class="empty-state">
    <i class="bi bi-chat-dots"></i>
    <div class="empty-state-title">No Messages Yet</div>
    <div class="empty-state-sub">Send your first message above.</div>
</div>
@endforelse
@endsection
