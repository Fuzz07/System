@php
    $pageTitle    = 'SSC Officers';
    $pageSubtitle = 'Meet Your Leaders';
@endphp
@extends('layouts.mobile-student')

@section('content')

<div class="hero-banner">
    <div class="hero-banner-title">Meet the SSC</div>
    <div class="hero-banner-sub">Dedicated leaders of your Supreme Student Council</div>
</div>

@forelse($officers as $officer)

@if($loop->first)
<div class="officer-grid">
@endif

<a href="mailto:{{ $officer->email }}" class="officer-card ripple">
    <div class="officer-avatar">
        @if(!empty($officer->profile_pic))
            <img src="{{ asset('assets/img/' . $officer->profile_pic) }}" alt="{{ $officer->fullname }}">
        @else
            {{ strtoupper(substr($officer->fullname, 0, 1)) }}
        @endif
    </div>
    <div class="officer-name">{{ $officer->fullname }}</div>
    <div>
        <span class="officer-role-badge president" style="background: rgba(227, 79, 38, 0.08); color: #e34f26; border: 1px solid rgba(227, 79, 38, 0.15); font-weight: 700; border-radius: 20px; font-size: 0.72rem; padding: 4px 10px; display: inline-block;">
            {{ $officer->position ?? ucfirst($officer->role) }}
        </span>
    </div>
    @if($officer->party)
    <div style="font-size: 0.75rem; color: #f06529; font-weight: 600; margin-top: 4px; display: flex; align-items: center; justify-content: center; gap: 4px;"><i class="bi bi-flag"></i> {{ $officer->party }}</div>
    @endif
    @if($officer->department)
    <div class="officer-dept"><i class="bi bi-building"></i> {{ Str::limit($officer->department, 24) }}</div>
    @endif
    <div class="officer-contact"><i class="bi bi-envelope-fill"></i> Contact</div>
</a>

@if($loop->last)
</div>{{-- /.officer-grid --}}
@endif

@empty
<div class="empty-state">
    <i class="bi bi-people"></i>
    <div class="empty-state-title">No Officers Listed</div>
    <div class="empty-state-sub">Officer profiles will appear here.</div>
</div>
@endforelse
@endsection
