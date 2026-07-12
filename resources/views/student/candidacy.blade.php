@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-student') @endsection

@section('content')
<div class="page-header">
    <div>
        <h1>SSC Officer Candidacy</h1>
        <p>Apply to run as a student representative or officer for the Supreme Student Council</p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        @php
            $activeSy = \App\Models\SchoolYear::where('is_active', 1)->first();
            $candidacy = Auth::user()->candidacies()->where('school_year', $activeSy->label ?? '')->first();
        @endphp

        @if(!$activeSy)
            <div class="alert alert-warning border-0 shadow-sm" style="border-radius:16px;">
                <i class="bi bi-exclamation-triangle-fill"></i> No active school year set. Candidacy applications are unavailable.
            </div>
        @else
            @if($candidacy)
                {{-- Candidacy Status Card --}}
                <div class="card border-0 shadow-sm" style="border-radius:24px; overflow:hidden;">
                    <div class="card-header-custom bg-light p-4 border-0">
                        <h4 class="mb-0 fw-bold" style="color:var(--navy-900);">Your Candidacy Status</h4>
                    </div>
                    <div class="card-body-custom p-4">
                        <div class="d-flex align-items-center gap-3 mb-4 p-3 rounded-4" style="background:var(--slate-50); border:1px solid var(--slate-100);">
                            <div style="font-size:2rem;">🗳️</div>
                            <div>
                                <div class="fw-bold text-dark mb-1" style="font-size:1.1rem;">Applied for: {{ $candidacy->position }}</div>
                                <div class="text-muted" style="font-size:0.82rem;">Department: {{ $candidacy->department }} · SY {{ $candidacy->school_year }}</div>
                            </div>
                            <div class="ms-auto">
                                @if($candidacy->status === 'pending')
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-4 py-2" style="border-radius:12px; font-weight:700;">Pending Dean Review</span>
                                @elseif($candidacy->status === 'approved')
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-4 py-2" style="border-radius:12px; font-weight:700;">Dean Selected & Voted</span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-4 py-2" style="border-radius:12px; font-weight:700;">Filing Rejected</span>
                                @endif
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6 class="fw-bold text-dark mb-2">My Platform / Manifesto</h6>
                            <div class="p-3 bg-light rounded-3 text-muted" style="font-size:0.9rem; line-height:1.6; white-space:pre-wrap;">{{ $candidacy->platform }}</div>
                        </div>

                        <div class="alert alert-info border-0 mb-0" style="border-radius:16px; font-size:0.85rem;">
                            @if($candidacy->status === 'pending')
                                <i class="bi bi-clock-history"></i> Your application is currently awaiting review by the Dean of <strong>{{ $candidacy->department }}</strong>. Once they cast their vote or endorsement, your status will update.
                            @elseif($candidacy->status === 'approved')
                                <i class="bi bi-patch-check-fill"></i> Congratulations! The Dean of your department has voted for and selected you to run as a representative for <strong>{{ $candidacy->department }}</strong> in the upcoming SSC elections.
                            @else
                                <i class="bi bi-x-circle-fill"></i> Your application has been declined by the department Dean. Please contact your department administration for further inquiries.
                            @endif
                        </div>
                    </div>
                </div>
            @elseif(!$activeSy->candidacy_open)
                {{-- Candidacy Filing Closed Card --}}
                <div class="card border-0 shadow-sm text-center p-5" style="border-radius:24px;">
                    <div style="font-size:4rem; margin-bottom:20px; opacity:0.6;">🔒</div>
                    <h3 class="fw-bold text-dark mb-3">Filing is Currently Closed</h3>
                    <p class="text-muted mx-auto" style="max-width:500px; font-size:0.92rem; line-height:1.6;">
                        SSC Officer candidacy filing is closed at this time. Announcements will be posted system-wide once candidacy applications open for the next election cycle.
                    </p>
                </div>
            @else
                {{-- Candidacy Filing Form --}}
                <div class="card border-0 shadow-sm" style="border-radius:24px; overflow:hidden;">
                    <div class="card-header-custom bg-light p-4 border-0">
                        <h4 class="mb-0 fw-bold" style="color:var(--navy-900);">Submit Your Candidacy Application</h4>
                    </div>
                    <div class="card-body-custom p-4">
                        <div class="alert alert-primary border-0 mb-4" style="border-radius:16px; font-size:0.85rem; line-height:1.5;">
                            <i class="bi bi-info-circle-fill"></i> You are applying to represent <strong>{{ Auth::user()->department }}</strong>. Your application will be sent to the Dean of your department for review, endorsement, and selection.
                        </div>

                        <form method="POST" action="{{ route('student.candidacy.store') }}">
                            @csrf
                            <input type="hidden" name="department" value="{{ Auth::user()->department }}">

                            <div class="mb-3">
                                <label class="form-label-custom">Select Target Position</label>
                                <select name="position" class="form-select-custom" required>
                                    <option value="{{ Auth::user()->department }} Representative">{{ Auth::user()->department }} Representative</option>
                                    <option value="SSC President">SSC President</option>
                                    <option value="SSC Vice President">SSC Vice President</option>
                                    <option value="SSC Secretary">SSC Secretary</option>
                                    <option value="SSC Treasurer">SSC Treasurer</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label-custom">Platform / Manifesto</label>
                                <textarea name="platform" class="form-control-custom" rows="6" placeholder="Explain your platform, key projects, and why you should be selected..." required minlength="20"></textarea>
                                <div class="form-text mt-1 text-muted" style="font-size:0.75rem;">Minimum of 20 characters. Outline your platform clearly.</div>
                            </div>

                            <button type="submit" class="btn-primary-custom w-100 justify-content-center py-3" style="border-radius:14px; font-weight:700;">
                                <i class="bi bi-send-check"></i> Submit Candidacy Application
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>
@endsection

@section('chatbot') @include('partials.chatbot') @endsection
