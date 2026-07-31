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
<div class="page-header d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h1>Election Results Dashboard</h1>
        <p>Official tally of student votes cast for Supreme Student Council candidacies</p>
    </div>
    @if($activeSy && $activeSy->results_announced)
        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-4 py-2" style="font-size:0.9rem; border-radius:12px;">
            <i class="bi bi-shield-check-fill me-1"></i> Final Results Announced
        </span>
    @else
        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-4 py-2" style="font-size:0.9rem; border-radius:12px;">
            <i class="bi bi-clock me-1"></i> Live Tally Active
        </span>
    @endif
</div>

<div class="row justify-content-center">
    <div class="col-md-10">
        @if(!$activeSy)
            <div class="alert alert-warning border-0 shadow-sm rounded-4">
                <i class="bi bi-exclamation-triangle-fill"></i> No active school year set. Election data is unavailable.
            </div>
        @else
            <div class="mb-4 p-4 bg-white border-0 shadow-sm rounded-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div style="font-size: 2rem;">📊</div>
                    <div>
                        <h5 class="mb-1 fw-bold text-dark">Vote Distribution (SY {{ $activeSy->label }})</h5>
                        <p class="text-muted mb-0 small">Real-time vote counts and visualization for all approved positions.</p>
                    </div>
                </div>
                
                {{-- Tabs Toggle for List View vs Chart View --}}
                <div class="nav nav-pills" id="resultsTab" role="tablist" style="background:#f1f5f9; padding:4px; border-radius:10px;">
                    <button class="nav-link active px-3 py-1.5 fw-bold small" id="list-tab" data-bs-toggle="pill" data-bs-target="#list-view" type="button" role="tab" style="border-radius:8px;">
                        <i class="bi bi-list-ol me-1"></i> Detailed Tally
                    </button>
                    <button class="nav-link px-3 py-1.5 fw-bold small" id="chart-tab" data-bs-toggle="pill" data-bs-target="#chart-view" type="button" role="tab" style="border-radius:8px;" onclick="initializeCharts()">
                        <i class="bi bi-pie-chart-fill me-1"></i> Visual Analytics
                    </button>
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
                <div class="tab-content" id="resultsTabContent">
                    {{-- 1. DETAILED TALLY LIST VIEW --}}
                    <div class="tab-pane fade show active" id="list-view" role="tabpanel" aria-labelledby="list-tab">
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
                                                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning" style="border: 2px solid white; font-size:0.65rem;" title="Current Winner/Leader">
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
                    </div>

                    {{-- 2. VISUAL CHARTS VIEW (using Chart.js) --}}
                    <div class="tab-pane fade" id="chart-view" role="tabpanel" aria-labelledby="chart-tab">
                        <div class="row g-4">
                            @foreach($candidatesByPosition as $pos => $candidates)
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm h-100" style="border-radius: 20px;">
                                        <div class="card-header bg-light py-3 px-4 border-0">
                                            <h6 class="mb-0 fw-bold text-dark">{{ $pos }} Distribution</h6>
                                        </div>
                                        <div class="card-body p-4 d-flex align-items-center justify-content-center" style="min-height: 300px;">
                                            @php
                                                $totalVotesForPos = collect($candidates)->sum('votes_count');
                                            @endphp
                                            @if($totalVotesForPos == 0)
                                                <div class="text-center text-muted small">No votes cast yet for this position.</div>
                                            @else
                                                <div style="width: 100%; max-width: 280px; margin: 0 auto;">
                                                    <canvas id="chart-{{ Str::slug($pos) }}"></canvas>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>

{{-- Load Chart.js from CDN & Render Beautiful Charts --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let chartsInitialized = false;
    const chartsMap = {};

    function initializeCharts() {
        if (chartsInitialized) return;
        chartsInitialized = true;

        setTimeout(() => {
            @foreach($candidatesByPosition as $pos => $candidates)
                @php
                    $totalVotesForPos = collect($candidates)->sum('votes_count');
                    $candNames = collect($candidates)->map(fn($c) => $c->user->fullname)->toArray();
                    $candVotes = collect($candidates)->map(fn($c) => $c->votes_count)->toArray();
                @endphp
                
                @if($totalVotesForPos > 0)
                    const ctx_{{ Str::slug($pos) }} = document.getElementById("chart-{{ Str::slug($pos) }}")?.getContext('2d');
                    if (ctx_{{ Str::slug($pos) }}) {
                        chartsMap["{{ Str::slug($pos) }}"] = new Chart(ctx_{{ Str::slug($pos) }}, {
                            type: 'doughnut',
                            data: {
                                labels: {!! json_encode($candNames) !!},
                                datasets: [{
                                    data: {!! json_encode($candVotes) !!},
                                    backgroundColor: [
                                        '#4f46e5', // Indigo
                                        '#10b981', // Emerald
                                        '#f59e0b', // Amber
                                        '#0ea5e9', // Sky Blue
                                        '#ec4899', // Pink
                                        '#8b5cf6'  // Purple
                                    ],
                                    borderWidth: 2,
                                    borderColor: '#ffffff'
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: {
                                            boxWidth: 12,
                                            padding: 15,
                                            font: {
                                                family: 'Inter, sans-serif',
                                                size: 11
                                            }
                                        }
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                const label = context.label || '';
                                                const value = context.parsed || 0;
                                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                                const pct = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                                return ` ${label}: ${value} votes (${pct}%)`;
                                            }
                                        }
                                    }
                                },
                                cutout: '65%'
                            }
                        });
                    }
                @endif
            @endforeach
        }, 150);
    }
</script>
@endsection
