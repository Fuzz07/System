@extends('layouts.mobile-student', ['pageTitle' => 'Election Results', 'showBack' => true, 'backUrl' => route('mobile.student.proposals')])

@section('content')
<div style="padding: 12px 16px;">
    {{-- School-year archive. Only years that actually ran an election are listed,
         so every option leads somewhere. Hidden when there is just the one. --}}
    @if($archivedYears->count() > 1)
        <form method="GET" action="{{ url()->current() }}" style="margin-bottom: 14px;">
            <label for="syPicker" style="display:block; font-size:0.7rem; font-weight:700; text-transform:uppercase; color:var(--slate-500); margin-bottom:6px;">
                <i class="bi bi-archive"></i> School Year
            </label>
            <select name="sy" id="syPicker" onchange="this.form.submit()"
                style="width:100%; border:1px solid var(--slate-200); border-radius:12px; padding:11px 12px; font-size:0.85rem; font-weight:700; font-family:inherit; background:#fff; color:var(--slate-800);">
                @foreach($archivedYears as $year)
                    <option value="{{ $year->label }}" @selected($selectedSy && $selectedSy->label === $year->label)>
                        SY {{ $year->label }}@if($activeSy && $year->label === $activeSy->label) (Current)@endif
                    </option>
                @endforeach
            </select>
            <noscript>
                <button type="submit" style="width:100%; margin-top:8px; padding:10px; background:var(--primary); color:#fff; border:none; border-radius:10px; font-weight:700;">View</button>
            </noscript>
        </form>
    @endif

    @if(!$selectedSy)
        <div class="m-alert m-alert-warning">
            <i class="bi bi-exclamation-triangle-fill"></i>
            No election results on record yet. They appear here once a dean approves candidacies and voting begins.
        </div>
    @else
        <div style="background: {{ $isArchive ? 'rgba(100, 116, 139, 0.1)' : 'rgba(59, 130, 246, 0.08)' }}; border-radius: 14px; padding: 14px; margin-bottom: 18px; font-size: 0.8rem; color: {{ $isArchive ? 'var(--slate-700)' : 'var(--primary-dark)' }}; line-height: 1.45;">
            <strong>
                @if($isArchive)<i class="bi bi-archive-fill"></i> Archived @endif
                Election Results — SY {{ $selectedSy->label }}
            </strong><br>
            {{ $isArchive
                ? 'Final tally kept on record for this school year.'
                : 'Approved candidates are ranked by votes cast. Your vote is included in the totals.' }}
        </div>

        {{-- Tabs for Detailed Tally vs Visual Analytics --}}
        <div style="display:flex; background:#e2e8f0; padding:3px; border-radius:12px; margin-bottom:16px;">
            <button type="button" id="mobile-tally-tab" class="m-tab-btn" onclick="switchMobileTab('tally')" 
                style="flex:1; padding:9px 12px; border:none; border-radius:10px; font-size:0.8rem; font-weight:700; background:#fff; color:#0f172a; cursor:pointer; box-shadow:0 2px 6px rgba(0,0,0,0.06); transition:all 0.2s;">
                <i class="bi bi-list-ol me-1"></i> Detailed Tally
            </button>
            <button type="button" id="mobile-chart-tab" class="m-tab-btn" onclick="switchMobileTab('charts')" 
                style="flex:1; padding:9px 12px; border:none; border-radius:10px; font-size:0.8rem; font-weight:700; background:transparent; color:#64748b; cursor:pointer; transition:all 0.2s;">
                <i class="bi bi-pie-chart-fill me-1"></i> Visual Analytics
            </button>
        </div>

        @if(empty($candidatesByPosition))
            <div style="text-align:center; padding: 18px 12px; color: var(--slate-400); background: #fff; border-radius: 16px; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);">
                <div style="font-size: 2rem; margin-bottom: 12px;">📊</div>
                <div style="font-weight: 700; margin-bottom: 6px;">No approved candidates yet</div>
                <div style="font-size: 0.78rem;">Election results will appear here once the dean approves candidacies and student voting begins.</div>
            </div>
        @else
            {{-- Tab 1: Detailed Tally View --}}
            <div id="mobile-tally-view" style="display:flex; flex-direction:column; gap:16px;">
                @foreach($candidatesByPosition as $position => $candidates)
                    @php
                        $totalVotes = collect($candidates)->sum('votes_count');
                        $maxVotes = collect($candidates)->max('votes_count');
                    @endphp
                    <div style="background:#fff; border-radius:18px; padding:16px; box-shadow:0 10px 30px rgba(15, 23, 42, 0.04);">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                            <div>
                                <div style="font-size:0.9rem; font-weight:800; color:#0f172a;">{{ $position }}</div>
                                <div style="font-size:0.74rem; color:#64748b;">{{ number_format($totalVotes) }} total vote{{ $totalVotes !== 1 ? 's' : '' }}</div>
                            </div>
                            <span style="font-size:0.72rem; color:#0f172a; background:rgba(59,130,246,0.12); padding:6px 10px; border-radius:999px;">Ranked</span>
                        </div>
                        <div style="display:flex; flex-direction:column; gap:12px;">
                            @foreach($candidates as $index => $cand)
                                @php
                                    $pct = $totalVotes > 0 ? round(($cand->votes_count / $totalVotes) * 100, 1) : 0;
                                    $leading = $totalVotes > 0 && $cand->votes_count === $maxVotes && $cand->votes_count > 0;
                                @endphp
                                <div style="border:1px solid #e2e8f0; border-radius:14px; padding:12px;">
                                    <div style="display:flex; justify-content:space-between; gap:12px; align-items:center; margin-bottom:10px;">
                                        <div style="display:flex; align-items:center; gap:10px;">
                                            <div class="avatar bg-primary text-white d-flex align-items-center justify-content-center" style="width:38px; height:38px; border-radius:50%; font-size:0.95rem;">@if($cand->photo_url)
                                                <img src="{{ $cand->photo_url }}" alt="{{ $cand->user->fullname }}" style="width:100%; height:100%; border-radius:inherit; object-fit:cover; display:block;">
                                            @else
                                                {{ $cand->user->avatar }}
                                            @endif</div>
                                            <div>
                                                <div style="font-size:0.84rem; font-weight:700; color:#111827;">{{ $cand->user->fullname }}</div>
                                                <div style="font-size:0.7rem; color:#6b7280;">{{ $cand->department }}</div>
                                            </div>
                                        </div>
                                        <div style="text-align:right;">
                                            <div style="font-size:0.88rem; font-weight:800; color:#0f172a;">{{ number_format($cand->votes_count) }}</div>
                                            <div style="font-size:0.68rem; color:#6b7280;">{{ $pct }}%</div>
                                        </div>
                                    </div>
                                    <div style="height:6px; background:#e2e8f0; border-radius:999px; overflow:hidden;">
                                        <div style="width: {{ $pct }}%; background: {{ $leading ? '#16a34a' : '#3b82f6' }}; height:100%; transition: width 0.3s ease;"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Tab 2: Visual Analytics View --}}
            <div id="mobile-charts-view" style="display:none; flex-direction:column; gap:16px;">
                @foreach($candidatesByPosition as $position => $candidates)
                    @php
                        $totalVotes = collect($candidates)->sum('votes_count');
                        $slug = Str::slug($position);
                    @endphp
                    <div style="background:#fff; border-radius:18px; padding:16px; box-shadow:0 10px 30px rgba(15, 23, 42, 0.04);">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                            <div>
                                <div style="font-size:0.9rem; font-weight:800; color:#0f172a;">{{ $position }}</div>
                                <div style="font-size:0.74rem; color:#64748b;">{{ number_format($totalVotes) }} total vote{{ $totalVotes !== 1 ? 's' : '' }}</div>
                            </div>
                            <span style="font-size:0.72rem; color:#4f46e5; background:rgba(79,70,229,0.1); padding:6px 10px; border-radius:999px; font-weight:700;">
                                <i class="bi bi-pie-chart-fill"></i> Distribution
                            </span>
                        </div>

                        @if($totalVotes == 0)
                            <div style="text-align:center; padding: 24px 12px; color:#94a3b8; font-size:0.78rem;">
                                No votes cast yet for this position.
                            </div>
                        @else
                            <div style="position:relative; width:100%; height:220px; margin:0 auto 12px;">
                                <canvas id="mobile-chart-{{ $slug }}"></canvas>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    @endif
</div>

<script>
    function switchMobileTab(tab) {
        var tallyBtn = document.getElementById('mobile-tally-tab');
        var chartBtn = document.getElementById('mobile-chart-tab');
        var tallyView = document.getElementById('mobile-tally-view');
        var chartsView = document.getElementById('mobile-charts-view');

        if (!tallyBtn || !chartBtn || !tallyView || !chartsView) return;

        if (tab === 'charts') {
            tallyBtn.style.background = 'transparent';
            tallyBtn.style.color = '#64748b';
            tallyBtn.style.boxShadow = 'none';

            chartBtn.style.background = '#fff';
            chartBtn.style.color = '#0f172a';
            chartBtn.style.boxShadow = '0 2px 6px rgba(0,0,0,0.06)';

            tallyView.style.display = 'none';
            chartsView.style.display = 'flex';
            renderMobileCharts();
        } else {
            chartBtn.style.background = 'transparent';
            chartBtn.style.color = '#64748b';
            chartBtn.style.boxShadow = 'none';

            tallyBtn.style.background = '#fff';
            tallyBtn.style.color = '#0f172a';
            tallyBtn.style.boxShadow = '0 2px 6px rgba(0,0,0,0.06)';

            chartsView.style.display = 'none';
            tallyView.style.display = 'flex';
        }
    }

    var mobileChartData = {
        @foreach($candidatesByPosition as $pos => $candidates)
            @php
                $candNames = collect($candidates)->map(fn($c) => $c->user->fullname)->toArray();
                $candVotes = collect($candidates)->map(fn($c) => (int)$c->votes_count)->toArray();
            @endphp
            "{{ Str::slug($pos) }}": {
                labels: {!! json_encode($candNames) !!},
                votes: {!! json_encode($candVotes) !!},
                total: {{ collect($candidates)->sum('votes_count') }}
            },
        @endforeach
    };

    var mobileActiveCharts = {};

    function ensureMobileChartJs(callback) {
        if (typeof Chart !== 'undefined') {
            callback();
            return;
        }
        var script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js';
        script.onload = callback;
        document.head.appendChild(script);
    }

    function renderMobileCharts() {
        ensureMobileChartJs(function() {
            Object.keys(mobileChartData).forEach(function(slug) {
                var data = mobileChartData[slug];
                if (!data || data.total <= 0) return;

                var canvas = document.getElementById('mobile-chart-' + slug);
                if (!canvas) return;

                if (mobileActiveCharts[slug]) {
                    mobileActiveCharts[slug].resize();
                    return;
                }

                var ctx = canvas.getContext('2d');
                mobileActiveCharts[slug] = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            data: data.votes,
                            backgroundColor: [
                                '#4f46e5', '#10b981', '#f59e0b', '#0ea5e9', '#ec4899', '#8b5cf6', '#14b8a6', '#f97316'
                            ],
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 10,
                                    padding: 10,
                                    font: { family: 'Plus Jakarta Sans, sans-serif', size: 11, weight: '600' }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        var label = context.label || '';
                                        var value = context.parsed || 0;
                                        var total = context.dataset.data.reduce(function(a, b) { return a + b; }, 0);
                                        var pct = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                        return ' ' + label + ': ' + value + ' votes (' + pct + '%)';
                                    }
                                }
                            }
                        },
                        cutout: '62%'
                    }
                });
            });
        });
    }
</script>
@endsection
