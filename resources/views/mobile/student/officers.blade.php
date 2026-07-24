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

<div class="officer-card ripple" onclick="openOfficerModal({{ $officer->id }})" style="cursor:pointer;">
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
    <div class="officer-contact"><i class="bi bi-person-lines-fill"></i> View Profile</div>
</div>

{{-- Custom Officer Profile Modal for Mobile --}}
<div id="officerModal{{ $officer->id }}" class="officer-modal-overlay" onclick="closeOfficerModal({{ $officer->id }})">
  <div class="officer-modal-card" onclick="event.stopPropagation();">
    <div class="officer-modal-banner"></div>
    <button type="button" class="btn-officer-modal-close" onclick="closeOfficerModal({{ $officer->id }})">
      <i class="bi bi-x"></i>
    </button>
    <div class="officer-modal-avatar-wrapper">
        @if(!empty($officer->profile_pic))
            <img src="{{ asset('assets/img/' . $officer->profile_pic) }}" alt="{{ $officer->fullname }}">
        @else
            <div class="officer-modal-initial">{{ strtoupper(substr($officer->fullname, 0, 1)) }}</div>
        @endif
    </div>
    <div class="officer-modal-body">
      <h3 class="officer-modal-name">{{ $officer->fullname }}</h3>
      <span class="officer-modal-role">{{ $officer->position ?? ucfirst($officer->role) }}</span>
      
      <div class="officer-modal-divider"></div>
      
      <div class="officer-modal-info-list">
        @if($officer->party)
        <div class="officer-modal-info-item">
          <div class="officer-modal-info-icon party"><i class="bi bi-flag-fill"></i></div>
          <div class="officer-modal-info-content">
            <div class="officer-modal-info-label">Political Party</div>
            <div class="officer-modal-info-value">{{ $officer->party }}</div>
          </div>
        </div>
        @endif
        
        <div class="officer-modal-info-item">
          <div class="officer-modal-info-icon dept"><i class="bi bi-building-fill"></i></div>
          <div class="officer-modal-info-content">
            <div class="officer-modal-info-label">Department</div>
            <div class="officer-modal-info-value">{{ $officer->department ?: 'SSC General Council' }}</div>
          </div>
        </div>
        
        <div class="officer-modal-info-item">
          <div class="officer-modal-info-icon year"><i class="bi bi-calendar3"></i></div>
          <div class="officer-modal-info-content">
            <div class="officer-modal-info-label">Year Level</div>
            <div class="officer-modal-info-value">{{ $officer->year_level ?: '—' }}</div>
          </div>
        </div>
        
        <div class="officer-modal-info-item">
          <div class="officer-modal-info-icon status"><i class="bi bi-shield-check"></i></div>
          <div class="officer-modal-info-content">
            <div class="officer-modal-info-label">Council Status</div>
            <div class="officer-modal-info-value">{{ ucfirst($officer->status) }}</div>
          </div>
        </div>
        
        <div class="officer-modal-info-item">
          <div class="officer-modal-info-icon email"><i class="bi bi-envelope-fill"></i></div>
          <div class="officer-modal-info-content">
            <div class="officer-modal-info-label">MS 365 Account</div>
            <div class="officer-modal-info-value" style="word-break: break-all; font-size: 0.82rem;">{{ $officer->email }}</div>
          </div>
        </div>
      </div>
      
      <a href="mailto:{{ $officer->email }}" class="btn-officer-modal-action">
        <i class="bi bi-envelope"></i> Send Email Message
      </a>
    </div>
  </div>
</div>

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

<style>
  .officer-modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 200000;
    background: rgba(15, 23, 42, 0.45);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    display: none;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.25s ease;
    padding: 20px;
  }
  .officer-modal-overlay.show {
    display: flex;
    opacity: 1;
  }
  .officer-modal-card {
    background: #ffffff;
    border-radius: 28px;
    width: 100%;
    max-width: 380px;
    overflow: hidden;
    position: relative;
    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.2);
    transform: scale(0.9);
    transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
  }
  .officer-modal-overlay.show .officer-modal-card {
    transform: scale(1);
  }
  .officer-modal-banner {
    height: 90px;
    background: linear-gradient(135deg, #4f46e5, #312e81);
  }
  .btn-officer-modal-close {
    position: absolute;
    top: 16px;
    right: 16px;
    background: rgba(255, 255, 255, 0.2);
    border: none;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 1.1rem;
    cursor: pointer;
    transition: background 0.2s;
  }
  .btn-officer-modal-close:hover {
    background: rgba(255, 255, 255, 0.35);
  }
  .officer-modal-avatar-wrapper {
    margin-top: -45px;
    display: flex;
    justify-content: center;
    position: relative;
    z-index: 5;
  }
  .officer-modal-avatar-wrapper img {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    border: 4px solid #ffffff;
    object-fit: cover;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  }
  .officer-modal-initial {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    border: 4px solid #ffffff;
    background: linear-gradient(135deg, #6366f1, #4f46e5);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.2rem;
    font-weight: 700;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  }
  .officer-modal-body {
    padding: 24px;
    text-align: center;
  }
  .officer-modal-name {
    font-size: 1.25rem;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 4px;
  }
  .officer-modal-role {
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    color: #4f46e5;
    background: rgba(79, 70, 229, 0.06);
    padding: 4px 12px;
    border-radius: 20px;
    display: inline-block;
    letter-spacing: 0.3px;
  }
  .officer-modal-divider {
    height: 1px;
    background: #e2e8f0;
    margin: 20px 0;
  }
  .officer-modal-info-list {
    text-align: left;
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-bottom: 24px;
  }
  .officer-modal-info-item {
    display: flex;
    align-items: center;
    gap: 14px;
  }
  .officer-modal-info-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.05rem;
  }
  .officer-modal-info-icon.party { background: rgba(239, 68, 68, 0.08); color: #ef4444; }
  .officer-modal-info-icon.dept { background: rgba(79, 70, 229, 0.08); color: #4f46e5; }
  .officer-modal-info-icon.year { background: rgba(16, 185, 129, 0.08); color: #10b981; }
  .officer-modal-info-icon.status { background: rgba(245, 158, 11, 0.08); color: #f59e0b; }
  .officer-modal-info-icon.email { background: rgba(6, 182, 212, 0.08); color: #06b6d4; }
  
  .officer-modal-info-content {
    flex: 1;
  }
  .officer-modal-info-label {
    font-size: 0.68rem;
    text-transform: uppercase;
    font-weight: 700;
    color: #94a3b8;
    letter-spacing: 0.3px;
  }
  .officer-modal-info-value {
    font-size: 0.88rem;
    font-weight: 600;
    color: #334155;
    margin-top: 1px;
  }
  .btn-officer-modal-action {
    background: linear-gradient(135deg, #4f46e5, #4338ca);
    color: #ffffff;
    border: none;
    border-radius: 14px;
    width: 100%;
    padding: 12px;
    font-size: 0.92rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    text-decoration: none;
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
    transition: transform 0.2s, box-shadow 0.2s;
  }
  .btn-officer-modal-action:active {
    transform: scale(0.98);
    box-shadow: 0 2px 6px rgba(79, 70, 229, 0.15);
  }
</style>

@push('scripts')
<script>
  function openOfficerModal(id) {
    const modal = document.getElementById('officerModal' + id);
    if (modal) {
      modal.classList.add('show');
      document.body.style.overflow = 'hidden';
    }
  }

  function closeOfficerModal(id) {
    const modal = document.getElementById('officerModal' + id);
    if (modal) {
      modal.classList.remove('show');
      document.body.style.overflow = '';
    }
  }
</script>
@endpush

@endsection
