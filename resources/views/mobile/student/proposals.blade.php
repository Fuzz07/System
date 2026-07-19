@php
    $pageTitle = 'Projects';
    $pageSubtitle = 'SSC Active Initiatives';
@endphp
@extends('layouts.mobile-student')

@section('content')

{{-- Hero Banner --}}
<div class="hero-banner">
    <div class="hero-banner-title">SSC Projects</div>
    <div class="hero-banner-sub">{{ $proposals->count() }} active initiative{{ $proposals->count() !== 1 ? 's' : '' }} from your Student Council</div>
</div>

@php
    $activeSy = \App\Models\SchoolYear::where('is_active', 1)->first();
    $candidacy = Auth::user()->candidacies()->where('school_year', $activeSy->label ?? '')->first();
@endphp

@if($activeSy && ($activeSy->candidacy_open || $candidacy))
    <div class="m-card elevated" style="margin-bottom: 20px; background: linear-gradient(135deg, #4f46e5 0%, #0ea5e9 100%); color: #fff; border: none; padding: 16px; border-radius: 16px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="font-size: 1.8rem;">🗳️</div>
            <div style="flex: 1;">
                @if($candidacy)
                    <div style="font-size: 0.88rem; font-weight: 700;">SSC Candidacy Submitted</div>
                    <div style="font-size: 0.72rem; opacity: 0.9;">Status: 
                        @if($candidacy->status === 'pending') Pending Dean Review
                        @elseif($candidacy->status === 'approved') Selected to Run
                        @else Declined
                        @endif
                    </div>
                @else
                    <div style="font-size: 0.88rem; font-weight: 700;">Officer Filing is Open!</div>
                    <div style="font-size: 0.72rem; opacity: 0.9;">Apply now to represent {{ Auth::user()->department }}</div>
                @endif
            </div>
            <a href="{{ route('mobile.student.candidacy') }}" style="text-decoration: none; font-size: 0.78rem; padding: 6px 12px; background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.4); border-radius: 8px; color: #fff; font-weight: 700; white-space: nowrap; display: flex; align-items: center; gap: 4px;">
                @if($candidacy) View @else Apply @endif <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
@endif

@php
    $hasApprovedCandidates = $activeSy ? \App\Models\Candidacy::where('school_year', $activeSy->label)->where('status', 'approved')->exists() : false;
@endphp

@if($hasApprovedCandidates)
    <div class="m-card elevated" style="margin-bottom: 20px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #fff; border: none; padding: 16px; border-radius: 16px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="font-size: 1.8rem;">🗳️</div>
            <div style="flex: 1;">
                <div style="font-size: 0.88rem; font-weight: 700;">SSC Elections are Live!</div>
                <div style="font-size: 0.72rem; opacity: 0.9;">Cast your vote for the new student council leaders.</div>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <a href="{{ route('mobile.student.voting') }}" style="text-decoration: none; font-size: 0.78rem; padding: 6px 12px; background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.4); border-radius: 8px; color: #fff; font-weight: 700; white-space: nowrap; display: flex; align-items: center; gap: 4px;">
                    Vote Now <i class="bi bi-arrow-right"></i>
                </a>
                <a href="{{ route('mobile.student.election.results') }}" style="text-decoration: none; font-size: 0.78rem; padding: 6px 12px; background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.25); border-radius: 8px; color: #fff; font-weight: 700; white-space: nowrap; display: flex; align-items: center; gap: 4px;">
                    View Results <i class="bi bi-bar-chart"></i>
                </a>
            </div>
        </div>
    </div>
@endif

@if($proposals->isEmpty())
<div class="empty-state">
    <i class="bi bi-folder2-open"></i>
    <div class="empty-state-title">No Projects Yet</div>
    <div class="empty-state-sub">Approved proposals will appear here.</div>
</div>

@else

{{-- Approved Projects --}}
@php $approved = $proposals->where('status', 'Approved'); @endphp
@if($approved->count())
<div class="section-header">
    <div class="section-title"><i class="bi bi-check-circle-fill" style="color:#10b981;margin-right:5px;"></i>Approved</div>
    <div class="section-title">{{ $approved->count() }}</div>
</div>

@foreach($approved as $p)
<a href="{{ route('mobile.student.proposal.show', $p) }}" class="project-card ripple">
    <div class="project-card-header">
        <div class="project-card-title">{{ $p->project_title }}</div>
        <div style="flex-shrink:0;">
            @if($p->project_status === 'Completed')
            <span class="m-badge done"><i class="bi bi-check2-circle"></i> Done</span>
            @else
            <span class="m-badge approved">Active</span>
            @endif
        </div>
    </div>
    <div class="project-card-desc">{{ $p->description }}</div>
    <div class="project-card-footer">
        <div class="project-lead">
            <div class="project-lead-avatar">{{ strtoupper(substr($p->officer->fullname ?? 'N', 0, 1)) }}</div>
            <span>{{ Str::limit($p->officer->fullname ?? 'N/A', 20) }}</span>
        </div>
        <div class="project-meta">
            <span><i class="bi bi-chat-text"></i> {{ $p->comments_count }}</span>
            <span><i class="bi bi-chevron-right"></i></span>
        </div>
    </div>
</a>
@endforeach
@endif

{{-- Pending Projects --}}
@php $pending = $proposals->where('status', 'Pending'); @endphp
@if($pending->count())
<div class="section-header" style="margin-top:24px;">
    <div class="section-title"><i class="bi bi-clock-fill" style="color:#f59e0b;margin-right:5px;"></i>Under Review</div>
    <div class="section-title">{{ $pending->count() }}</div>
</div>

@foreach($pending as $p)
<a href="{{ route('mobile.student.proposal.show', $p) }}" class="project-card ripple" style="border-left:3px solid #f59e0b;">
    <div class="project-card-header">
        <div class="project-card-title">{{ $p->project_title }}</div>
        <span class="m-badge pending">Review</span>
    </div>
    <div class="project-card-desc">{{ $p->description }}</div>
    <div class="project-card-footer">
        <div class="project-lead">
            <div class="project-lead-avatar" style="background:linear-gradient(135deg,#f59e0b,#d97706);">{{ strtoupper(substr($p->officer->fullname ?? 'N', 0, 1)) }}</div>
            <span>{{ Str::limit($p->officer->fullname ?? 'N/A', 20) }}</span>
        </div>
        <div class="project-meta">
            <span><i class="bi bi-chat-text"></i> {{ $p->comments_count }}</span>
            <span><i class="bi bi-chevron-right"></i></span>
        </div>
    </div>
</a>
@endforeach
@endif

@endif
@endsection
