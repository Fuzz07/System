@extends('layouts.app')

@section('sidebar-nav')
    @if(Auth::user()->isAdmin())
        @include('partials.sidebar-admin')
    @elseif(Auth::user()->isDean())
        @include('partials.sidebar-dean')
    @else
        @include('partials.sidebar-student')
    @endif
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1>Election Results Dashboard</h1>
        <p>Live tally of student votes cast for Supreme Student Council candidacies</p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-10">
        @if(!$activeSy)
            <div class="alert alert-warning border-0 shadow-sm rounded-4">
                <i class="bi bi-exclamation-triangle-fill"></i> No active school year set. Election data is unavailable.
            </div>
        @else
            <div class="mb-4 p-4 bg-white border-0 shadow-sm rounded-4 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div style="font-size: 2rem;">📊</div>
                    <div>
                        <h5 class="mb-1 fw-bold text-dark">Live Vote Tally (SY {{ $activeSy->label }})</h5>
                        <p class="text-muted mb-0 small">Updated in real-time. Showing approved candidates only.</p>
                    </div>
                </div>
                <div>
                    <span class="badge bg-primary px-3 py-2 rounded-pill"><i class="bi bi-activity"></i> Live Tally</span>
                </div>
            </div>

            @if(empty($candidatesByPosition))
                <div class="card border-0 shadow-sm text-center p-5" style="border-radius:24px;">
                    <div style="font-size:4rem; margin-bottom:20px; opacity:0.3;">🗳️</div>
                    <h4 class="fw-bold text-dark">No Active Candidates Found</h4>
                    <p class="text-muted mx-auto mb-0" style="max-width:500px; font-size:0.92rem;">
                        There are no approved candidates running for office in the active school year. Once candidacy filings are reviewed and approved, they will show up here.
                    </p>
                </div>
            @else
                <div class="row g-4">
                    @foreach($candidatesByPosition as $pos => $candidates)
                        @php
                            $totalVotesForPos = collect($candidates)->sum('votes_count');
                            $maxVotes = collect($candidates)->max('votes_count');
                        @endphp
                        
                        <div class="col-12">
                            <div class="card border-0 shadow-sm" style="border-radius: 20px; overflow: hidden;">
                                <div class="card-header bg-light py-3 px-4 border-0 d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0 fw-bold text-dark">{{ $pos }}</h5>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-3 py-1 rounded-pill small">
                                        {{ number_format($totalVotesForPos) }} Total Vote{{ $totalVotesForPos !== 1 ? 's' : '' }}
                                    </span>
                                </div>
                                <div class="card-body p-4">
                                    <div class="d-flex flex-column gap-4">
                                        @foreach($candidates as $index => $cand)
                                            @php
                                                $pct = $totalVotesForPos > 0 ? round(($cand->votes_count / $totalVotesForPos) * 100, 1) : 0;
                                                $isLeading = $totalVotesForPos > 0 && $cand->votes_count === $maxVotes && $cand->votes_count > 0;
                                            @endphp
                                            
                                            <div class="position-relative">
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="avatar bg-primary text-white fw-bold d-flex align-items-center justify-content-center position-relative" style="width:48px; height:48px; border-radius:50%; font-size:1.15rem;">
                                                            {{ $cand->user->avatar }}
                                                            @if($index === 0 && $cand->votes_count > 0)
                                                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning" style="border: 2px solid white; font-size:0.65rem;" title="Current Leader">
                                                                    🏆
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                                                {{ $cand->user->fullname }}
                                                                @if($isLeading)
                                                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2 py-0.5 rounded-pill" style="font-size: 0.65rem; font-weight:700;">Leading</span>
                                                                @endif
                                                            </div>
                                                            <div class="text-muted small" style="font-size:0.75rem;">Department: {{ $cand->department }}</div>
                                                        </div>
                                                    </div>
                                                    <div class="text-end">
                                                        <div class="fw-bold text-dark" style="font-size:1.1rem;">{{ number_format($cand->votes_count) }} vote{{ $cand->votes_count !== 1 ? 's' : '' }}</div>
                                                        <div class="text-muted small" style="font-size:0.75rem;">{{ $pct }}% of position votes</div>
                                                    </div>
                                                </div>
                                                
                                                <div class="progress" style="height: 10px; background-color: #f1f5f9; border-radius: 99px; overflow: hidden;">
                                                    <div class="progress-bar {{ $isLeading ? 'bg-success' : 'bg-primary' }}" 
                                                         role="progressbar" 
                                                         style="width: {{ $pct }}%; border-radius: 99px; transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);" 
                                                         aria-valuenow="{{ $pct }}" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100">
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
