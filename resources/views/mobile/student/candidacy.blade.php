@extends('layouts.mobile-student', ['pageTitle' => 'Apply as Officer', 'showBack' => true, 'backUrl' => route('mobile.student.proposals')])

@section('content')
@php
    $activeSy = \App\Models\SchoolYear::where('is_active', 1)->first();
    $candidacy = Auth::user()->candidacies()->where('school_year', $activeSy->label ?? '')->first();
@endphp

<div class="candidacy-container" style="padding: 4px 0;">

    @if(!$activeSy)
        <div class="m-alert m-alert-danger">
            <i class="bi bi-exclamation-triangle-fill"></i> No active school year set.
        </div>
    @else
        @if($candidacy)
            {{-- Status Screen --}}
            <div class="m-card elevated" style="padding: 20px 16px;">
                <div style="text-align: center; margin-bottom: 20px;">
                    <div style="font-size: 2.5rem; margin-bottom: 8px;">🗳️</div>
                    <h2 style="font-size: 1.15rem; font-weight: 800; color: var(--slate-900);">Filing Submitted</h2>
                    <p style="font-size: 0.76rem; color: var(--slate-500); margin-top: 3px;">SY {{ $candidacy->school_year }}</p>
                </div>

                <div style="background: var(--slate-50); border: 1px solid var(--slate-200); border-radius: 12px; padding: 14px; margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: var(--slate-500);">Application Status</span>
                        @if($candidacy->status === 'pending')
                            <span class="m-badge pending">Pending Dean Review</span>
                        @elseif($candidacy->status === 'approved')
                            <span class="m-badge approved">Selected to Run</span>
                        @else
                            <span class="m-badge rejected">Declined</span>
                        @endif
                    </div>
                    <div style="font-size: 0.9rem; font-weight: 700; color: var(--slate-800);">{{ $candidacy->position }}</div>
                    <div style="font-size: 0.74rem; color: var(--slate-400); margin-top: 2px;">Department: {{ $candidacy->department }}</div>
                </div>

                <div style="margin-bottom: 20px;">
                    <h3 style="font-size: 0.82rem; font-weight: 700; color: var(--slate-700); margin-bottom: 6px;">Platform Statement</h3>
                    <div style="font-size: 0.8rem; color: var(--slate-600); line-height: 1.5; background: var(--slate-50); padding: 12px; border-radius: 10px; white-space: pre-wrap;">{{ $candidacy->platform }}</div>
                </div>

                <div style="background: rgba(79, 70, 229, 0.06); border-radius: 10px; padding: 12px; font-size: 0.76rem; color: var(--primary-dark); line-height: 1.4;">
                    @if($candidacy->status === 'pending')
                        <i class="bi bi-hourglass-split"></i> Awaiting review by the Dean of <strong>{{ $candidacy->department }}</strong>. You will be notified here once updated.
                    @elseif($candidacy->status === 'approved')
                        <i class="bi bi-check-circle-fill"></i> Congratulations! The Dean of your department has selected you to run as representative in the elections.
                    @else
                        <i class="bi bi-x-circle-fill"></i> Your candidacy was not approved by the department Dean.
                    @endif
                </div>
            </div>
        @elseif(!$activeSy->candidacy_open)
            {{-- Closed Screen --}}
            <div class="m-card" style="text-align: center; padding: 40px 20px;">
                <div style="font-size: 3rem; margin-bottom: 16px; opacity: 0.7;">🔒</div>
                <h2 style="font-size: 1.15rem; font-weight: 800; color: var(--slate-900); margin-bottom: 8px;">Filing Closed</h2>
                <p style="font-size: 0.8rem; color: var(--slate-500); line-height: 1.5;">
                    SSC candidacy filing is currently closed. Keep an eye on announcements for dates of the next election filing cycle.
                </p>
            </div>
        @else
            {{-- Form Screen --}}
            <div class="m-card elevated" style="padding: 20px 16px;">
                <h2 style="font-size: 1.05rem; font-weight: 800; color: var(--slate-900); margin-bottom: 6px;">SSC Officer Candidacy</h2>
                <p style="font-size: 0.78rem; color: var(--slate-500); line-height: 1.4; margin-bottom: 20px;">
                    Fill out the application to submit your candidacy. Your submission will be reviewed by the department Dean.
                </p>

                <form method="POST" action="{{ route('mobile.student.candidacy.store') }}">
                    @csrf
                    <input type="hidden" name="department" value="{{ Auth::user()->department }}">

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 0.78rem; font-weight: 700; color: var(--slate-700); margin-bottom: 6px;">Department</label>
                        <input type="text" class="feedback-compose" style="width: 100%; border: 1px solid var(--slate-200); border-radius: 10px; padding: 10px 12px; font-size: 0.85rem; background: var(--slate-100); color: var(--slate-500);" value="{{ Auth::user()->department }}" readonly>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 0.78rem; font-weight: 700; color: var(--slate-700); margin-bottom: 6px;">Target Position</label>
                        <select name="position" style="width: 100%; border: 1px solid var(--slate-200); border-radius: 10px; padding: 10px 12px; font-size: 0.85rem; font-family: inherit; background: #fff;" required>
                            <option value="{{ Auth::user()->department }} Representative">{{ Auth::user()->department }} Representative</option>
                            <option value="SSC President">SSC President</option>
                            <option value="SSC Vice President">SSC Vice President</option>
                            <option value="SSC Secretary">SSC Secretary</option>
                            <option value="SSC Treasurer">SSC Treasurer</option>
                        </select>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-size: 0.78rem; font-weight: 700; color: var(--slate-700); margin-bottom: 6px;">Platform Manifesto</label>
                        <textarea name="platform" placeholder="Briefly state your vision, program of actions, or platform..." style="width: 100%; border: 1px solid var(--slate-200); border-radius: 10px; padding: 10px 12px; font-size: 0.85rem; font-family: inherit; background: var(--slate-50); resize: none; min-height: 120px;" required minlength="20"></textarea>
                    </div>

                    <button type="submit" class="install-close-btn" style="width: 100%; padding: 12px; background: var(--primary); color: #fff; border: none; border-radius: 10px; font-size: 0.9rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <i class="bi bi-send-check"></i> Submit Application
                    </button>
                </form>
            </div>
        @endif
    @endif

</div>
@endsection
