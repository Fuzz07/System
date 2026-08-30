@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-admin') @endsection

@section('content')
<div class="page-header">
    <div>
        <h1>SSC Candidacy Filings</h1>
        <p>Monitor student applications to run as SSC officers and check deans' selections across departments</p>
    </div>
</div>

{{-- Election Procedure Control Panel --}}
<div class="card border-0 shadow-sm mb-4" style="border-radius:16px; background:linear-gradient(135deg, #1e1b4b, #312e81); color:#ffffff;">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <span class="badge bg-primary bg-opacity-20 text-white mb-2 px-3 py-1.5" style="border: 1px solid rgba(255,255,255,0.15); font-size: 0.8rem; border-radius: 30px;">
                    <i class="bi bi-calendar3 me-1"></i> Active School Year: {{ $activeSy->label ?? 'N/A' }}
                </span>
                <h2 class="h3 fw-bold text-white mb-1">SSC Election Procedure Control Panel</h2>
                <p class="mb-0 text-white text-opacity-75 small">Administer the complete lifecycle of the supreme student council election.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if(!$activeSy)
                    <div class="text-warning fw-bold"><i class="bi bi-exclamation-triangle"></i> No Active School Year</div>
                @else
                    @if(!$activeSy->voting_open && !$activeSy->results_announced)
                        {{-- Nomination Phase / Ready to Open --}}
                        <div class="d-flex flex-column align-items-end text-end me-3">
                            <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill mb-1">Nomination Phase</span>
                            <span class="small text-white text-opacity-75">Filing is {{ $activeSy->candidacy_open ? 'OPEN' : 'CLOSED' }}</span>
                        </div>
                        <form method="POST" action="{{ route('admin.election.open') }}" onsubmit="return confirm('Are you sure you want to open the election? This starts an 8-hour voting period and notifies all eligible students!')">
                            @csrf
                            <button type="submit" class="btn btn-light fw-bold text-primary px-4 py-2" style="border-radius:10px; background: #ffffff; color: #4f46e5 !important; border:none; box-shadow:0 4px 12px rgba(0,0,0,0.15);">
                                <i class="bi bi-play-circle-fill me-1"></i> Open Voting (Start 8-Hours)
                            </button>
                        </form>
                    @elseif($activeSy->voting_open)
                        {{-- Voting active --}}
                        <div class="d-flex flex-column align-items-end text-end me-3">
                            <span class="badge bg-success text-white px-3 py-1.5 rounded-pill mb-1 animate-pulse"><i class="bi bi-broadcast me-1"></i> Voting Active</span>
                            <span class="small text-white text-opacity-75" id="election-ends-label">Ends at: {{ $activeSy->voting_ends_at?->format('h:i A') }}</span>
                        </div>
                        <div class="p-3 bg-white bg-opacity-10 rounded-3 text-center min-w-150 me-2" style="border:1px solid rgba(255,255,255,0.1);">
                            <div class="small text-white text-opacity-70 text-uppercase fw-semibold" style="font-size:0.7rem; letter-spacing:1px;">Time Remaining</div>
                            <div class="h4 fw-bold text-warning mb-0" id="voting-countdown" style="font-variant-numeric: tabular-nums;">--:--:--</div>
                        </div>
                        <form method="POST" action="{{ route('admin.election.close') }}" onsubmit="return confirm('Are you sure you want to manually close the voting period immediately?')">
                            @csrf
                            <button type="submit" class="btn btn-danger fw-bold text-white px-3 py-2" style="border-radius:10px; border:none; box-shadow:0 4px 12px rgba(220,38,38,0.3);">
                                <i class="bi bi-stop-fill"></i> Close Voting
                            </button>
                        </form>
                    @elseif($activeSy->results_announced)
                        {{-- Results announced --}}
                        <div class="d-flex flex-column align-items-end text-end me-3">
                            <span class="badge bg-info text-white px-3 py-1.5 rounded-pill mb-1"><i class="bi bi-check2-all me-1"></i> Concluded</span>
                            <span class="small text-white text-opacity-75">Officers list updated!</span>
                        </div>
                        <a href="{{ route('admin.election.results') }}" class="btn btn-outline-light fw-bold px-4 py-2" style="border-radius:10px;">
                            <i class="bi bi-bar-chart-fill me-1"></i> View Results & Charts
                        </a>
                    @else
                        {{-- Voting period ended but results not announced yet --}}
                        <div class="d-flex flex-column align-items-end text-end me-3">
                            <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill mb-1">Awaiting Announcement</span>
                            <span class="small text-white text-opacity-75">8 hours voting period ended</span>
                        </div>
                        <form method="POST" action="{{ route('admin.election.announce') }}" onsubmit="return confirm('Are you sure you want to announce election results? This will auto-promote winners as active SSC Officers, update user credentials, and post the winners board announcement!')">
                            @csrf
                            <button type="submit" class="btn btn-warning fw-bold text-dark px-4 py-2" style="border-radius:10px; box-shadow:0 4px 12px rgba(245,158,11,0.3);">
                                <i class="bi bi-trophy-fill me-1"></i> Announce Results & Promote Winners
                            </button>
                        </form>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>

@if($activeSy && $activeSy->voting_open && $activeSy->voting_ends_at)
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const endTime = new Date("{{ $activeSy->voting_ends_at->toIso8601String() }}").getTime();
        
        function updateCountdown() {
            const now = new Date().getTime();
            const distance = endTime - now;
            
            if (distance < 0) {
                document.getElementById("voting-countdown").innerHTML = "00:00:00";
                document.getElementById("voting-countdown").classList.remove("text-warning");
                document.getElementById("voting-countdown").classList.add("text-danger");
                // Optional: reload page once when timer hits 0 to refresh the panel state
                setTimeout(() => { location.reload(); }, 2000);
                return;
            }
            
            const hours = Math.floor(distance / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            const pad = (n) => n < 10 ? '0' + n : n;
            document.getElementById("voting-countdown").innerHTML = pad(hours) + ":" + pad(minutes) + ":" + pad(seconds);
        }
        
        updateCountdown();
        setInterval(updateCountdown, 1000);
    });
</script>
<style>
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
</style>
@endif

{{-- Stat Cards --}}
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="border-radius:16px;">
            <div class="card-body-custom p-4 d-flex align-items-center gap-3">
                <div style="width:48px;height:48px;background:rgba(79,70,229,0.1);color:var(--primary);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;"><i class="bi bi-people"></i></div>
                <div>
                    <h3 class="mb-1 fw-bold text-dark">{{ $stats['total'] }}</h3>
                    <p class="text-muted mb-0 small">Total Filings</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="border-radius:16px;">
            <div class="card-body-custom p-4 d-flex align-items-center gap-3">
                <div style="width:48px;height:48px;background:rgba(245,158,11,0.1);color:var(--warning);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;"><i class="bi bi-hourglass-split"></i></div>
                <div>
                    <h3 class="mb-1 fw-bold text-dark">{{ $stats['pending'] }}</h3>
                    <p class="text-muted mb-0 small">Pending Review</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="border-radius:16px;">
            <div class="card-body-custom p-4 d-flex align-items-center gap-3">
                <div style="width:48px;height:48px;background:rgba(16,185,129,0.1);color:var(--success);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;"><i class="bi bi-check-circle"></i></div>
                <div>
                    <h3 class="mb-1 fw-bold text-dark">{{ $stats['approved'] }}</h3>
                    <p class="text-muted mb-0 small">Dean Voted / Selected</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="border-radius:16px;">
            <div class="card-body-custom p-4 d-flex align-items-center gap-3">
                <div style="width:48px;height:48px;background:rgba(239,68,68,0.1);color:var(--danger);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;"><i class="bi bi-x-circle"></i></div>
                <div>
                    <h3 class="mb-1 fw-bold text-dark">{{ $stats['rejected'] }}</h3>
                    <p class="text-muted mb-0 small">Declined filings</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Candidacy Table with Year Filter --}}
<div class="card border-0 shadow-sm mb-5" style="border-radius:20px; overflow:hidden;">
    <div class="card-header-custom bg-light p-4 border-0 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <span class="card-title h5 mb-1 fw-bold text-dark d-block">
                <i class="bi bi-clipboard-check text-primary me-1"></i> Candidacy Filings
            </span>
            <span class="text-muted small">Viewing applications for {{ $selectedSy === 'all' ? 'All School Years' : 'School Year ' . ($selectedSy ?? 'N/A') }}</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <form method="GET" action="{{ route('admin.candidacies') }}" class="m-0 d-flex align-items-center gap-2">
                <label for="syFilter" class="small fw-bold text-muted mb-0 d-none d-sm-inline">School Year:</label>
                <select name="sy" id="syFilter" class="form-select form-select-sm fw-bold" onchange="this.form.submit()" style="border-radius:10px; min-width:160px; padding:0.45rem 1.8rem 0.45rem 0.8rem;">
                    @foreach($allSchoolYears as $syOption)
                        <option value="{{ $syOption->label }}" @selected($selectedSy === $syOption->label)>
                            SY {{ $syOption->label }}@if($activeSy && $activeSy->label === $syOption->label) (Active)@endif
                        </option>
                    @endforeach
                    <option value="all" @selected($selectedSy === 'all')>All School Years</option>
                </select>
            </form>
        </div>
    </div>
    <div class="card-body-custom p-0">
        <div class="table-responsive">
            <table class="table-custom mb-0">
                <thead>
                    <tr>
                        <th>Candidate</th>
                        <th>Department</th>
                        <th>Position</th>
                        <th>Manifesto</th>
                        <th>Dean Endorsement Status</th>
                        <th>School Year</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($candidacies as $c)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar bg-light text-primary d-flex align-items-center justify-content-center" style="width:40px; height:40px; border-radius:10px; font-weight:700; font-size:0.9rem; background:rgba(79,70,229,0.08);">
                                    @if($c->photo_url)
                                        <img src="{{ $c->photo_url }}" alt="{{ $c->user->fullname }}" style="width:100%; height:100%; border-radius:inherit; object-fit:cover; display:block;">
                                    @else
                                        {{ $c->user->avatar }}
                                    @endif
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">{{ $c->user->fullname }}</div>
                                    <div class="text-muted small">ID: {{ $c->user->student_id }} · Yr: {{ $c->user->year_level }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-secondary" style="font-size:0.75rem; border-radius:6px; font-weight:600;">{{ $c->department }}</span>
                        </td>
                        <td>
                            <div style="font-weight:700; color:var(--navy-900);">{{ $c->position }}</div>
                        </td>
                        <td>
                            <div class="text-truncate text-muted small" style="max-width:200px; cursor:pointer;" title="Click to expand" data-bs-toggle="collapse" data-bs-target="#platformCollapse{{ $c->id }}">
                                <i class="bi bi-eye"></i> View manifesto
                            </div>
                            <div class="collapse mt-2 p-3 bg-light rounded-3 text-muted small" id="platformCollapse{{ $c->id }}" style="white-space:pre-wrap; line-height:1.5;">
                                {{ $c->platform }}
                            </div>
                        </td>
                        <td>
                            @if($c->status === 'pending')
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-2" style="border-radius:8px;">Pending Dean Review</span>
                            @elseif($c->status === 'approved')
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2" style="border-radius:8px;"><i class="bi bi-check-lg"></i> Voted / Selected</span>
                            @else
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2" style="border-radius:8px;">Declined</span>
                            @endif
                        </td>
                        <td style="font-family:monospace; font-size:0.85rem;">
                            {{ $c->school_year }}
                        </td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('admin.candidacy.destroy', $c) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this candidacy application?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm" style="border-radius:8px; font-size:0.75rem; padding:6px 12px;">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-inbox" style="font-size:3rem; opacity:0.2;"></i>
                            <p class="mt-3 mb-0">No candidacy applications found for the selected school year.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Election Archive & Previous Winners Section --}}
<div class="mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h3 class="h4 fw-bold text-dark mb-1">
            <i class="bi bi-trophy-fill text-warning me-2"></i> Election Archive & Previous Winners
        </h3>
        <p class="text-muted small mb-0">Official record of elected student leaders and election outcomes archived across school years, sorted chronologically.</p>
    </div>
    <div>
        <a href="{{ route('admin.election.results') }}" class="btn btn-outline-primary btn-sm fw-bold px-3 py-2" style="border-radius:10px;">
            <i class="bi bi-bar-chart-fill me-1"></i> Full Results & Analytics Dashboard
        </a>
    </div>
</div>

<div class="d-flex flex-column gap-4 mb-5">
    @forelse($archivedElections as $arch)
        @php
            $sy = $arch['school_year'];
            $isCur = $arch['is_active'];
            $announced = $arch['results_announced'];
            $winners = $arch['winners'];
        @endphp
        <div class="card border-0 shadow-sm" style="border-radius:20px; overflow:hidden;">
            <div class="card-header bg-white p-4 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:48px; height:48px; border-radius:12px; background:{{ $announced ? 'rgba(16,185,129,0.1)' : ($isCur ? 'rgba(79,70,229,0.1)' : 'rgba(100,116,139,0.1)') }}; color:{{ $announced ? '#059669' : ($isCur ? '#4f46e5' : '#475569') }}; display:flex; align-items:center; justify-content:center; font-size:1.4rem;">
                        <i class="bi bi-{{ $announced ? 'trophy' : ($isCur ? 'broadcast' : 'archive') }}"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="fw-bold text-dark mb-0">School Year {{ $sy->label }}</h5>
                            @if($isCur)
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1 rounded-pill" style="font-size:0.75rem;">Active SY</span>
                            @endif
                            @if($announced)
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1 rounded-pill" style="font-size:0.75rem;"><i class="bi bi-check-circle-fill me-1"></i> Results Announced</span>
                            @elseif($isCur && $sy->voting_open)
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2.5 py-1 rounded-pill" style="font-size:0.75rem;"><i class="bi bi-clock-fill me-1"></i> Voting In Progress</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2.5 py-1 rounded-pill" style="font-size:0.75rem;">Archived Record</span>
                            @endif
                        </div>
                        <div class="text-muted small mt-1">
                            <i class="bi bi-people me-1"></i> {{ $arch['total_filings'] }} Candidac{{ $arch['total_filings'] === 1 ? 'y' : 'ies' }} Filed
                            <span class="mx-1.5">·</span>
                            <i class="bi bi-check2-square me-1"></i> {{ number_format($arch['total_votes']) }} Votes Cast
                        </div>
                    </div>
                </div>
                <div>
                    <a href="{{ route('admin.election.results', ['sy' => $sy->label]) }}" class="btn btn-outline-primary btn-sm fw-bold px-3 py-2" style="border-radius:10px;">
                        <i class="bi bi-bar-chart-fill me-1"></i> View SY {{ $sy->label }} Results
                    </a>
                </div>
            </div>

            <div class="card-body p-4 bg-light bg-opacity-50">
                @if(!empty($winners))
                    <div class="row g-3">
                        @foreach($winners as $win)
                            @php
                                $cand = $win['candidate'];
                            @endphp
                            <div class="col-md-6 col-lg-4">
                                <div class="p-3 bg-white border rounded-4 h-100 shadow-sm position-relative d-flex flex-column justify-content-between" style="border-color:#e2e8f0 !important;">
                                    <div>
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="badge bg-warning bg-opacity-15 text-warning-800 border border-warning border-opacity-25 px-2.5 py-1 rounded-pill fw-bold" style="font-size:0.7rem; color:#92400e !important;">
                                                <i class="bi bi-trophy-fill me-1 text-warning"></i> {{ $win['position'] }}
                                            </span>
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill fw-bold" style="font-size:0.68rem;">
                                                Winner
                                            </span>
                                        </div>

                                        <div class="d-flex align-items-center gap-3 my-2">
                                            <div class="avatar bg-primary text-white fw-bold d-flex align-items-center justify-content-center" style="width:46px; height:46px; border-radius:12px; font-size:1.1rem; flex-shrink:0;">
                                                @if($cand->photo_url)
                                                    <img src="{{ $cand->photo_url }}" alt="{{ $cand->user->fullname }}" style="width:100%; height:100%; border-radius:inherit; object-fit:cover; display:block;">
                                                @else
                                                    {{ $cand->user->avatar }}
                                                @endif
                                            </div>
                                            <div class="overflow-hidden">
                                                <div class="fw-bold text-dark text-truncate" style="font-size:0.92rem;">{{ $cand->user->fullname }}</div>
                                                <div class="text-muted small text-truncate" style="font-size:0.75rem;">
                                                    {{ $cand->department }} · ID: {{ $cand->user->student_id }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-2 pt-2 border-top d-flex align-items-center justify-content-between text-muted small" style="font-size:0.75rem;">
                                        <span><i class="bi bi-patch-check-fill text-success me-1"></i> Votes Received:</span>
                                        <strong class="text-dark">{{ number_format($win['votes_count']) }} ({{ $win['percentage'] }}%)</strong>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-info-circle" style="font-size:1.8rem; opacity:0.35;"></i>
                        <p class="mb-0 mt-2 small">
                            @if($isCur && !$announced)
                                Election is currently in progress. Final winners will be officially archived here once the election results are announced.
                            @else
                                No winning candidates recorded for School Year {{ $sy->label }}.
                            @endif
                        </p>
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="card border-0 shadow-sm text-center p-5" style="border-radius:20px;">
            <div style="font-size:3rem; opacity:0.3;">🗳️</div>
            <h5 class="fw-bold text-dark mt-3">No Election Archives on Record</h5>
            <p class="text-muted small mb-0">Past election results and winners will automatically be sorted and stored here per school year.</p>
        </div>
    @endforelse
</div>
@endsection
