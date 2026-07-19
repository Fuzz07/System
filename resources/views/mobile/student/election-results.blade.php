@extends('layouts.mobile-student', ['pageTitle' => 'Election Results', 'showBack' => true, 'backUrl' => route('mobile.student.proposals')])

@section('content')
<div style="padding: 12px 16px;">
    @if(!$activeSy)
        <div class="m-alert m-alert-danger">
            <i class="bi bi-exclamation-triangle-fill"></i> No active school year set. Election results are unavailable.
        </div>
    @else
        <div style="background: rgba(59, 130, 246, 0.08); border-radius: 14px; padding: 14px; margin-bottom: 18px; font-size: 0.8rem; color: var(--primary-dark); line-height: 1.45;">
            <strong>Election Results — SY {{ $activeSy->label }}</strong><br>
            Approved candidates are ranked by votes cast. Your vote is included in the totals.
        </div>

        @if(empty($candidatesByPosition))
            <div style="text-align:center; padding: 18px 12px; color: var(--slate-400); background: #fff; border-radius: 16px; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);">
                <div style="font-size: 2rem; margin-bottom: 12px;">📊</div>
                <div style="font-weight: 700; margin-bottom: 6px;">No approved candidates yet</div>
                <div style="font-size: 0.78rem;">Election results will appear here once the dean approves candidacies and student voting begins.</div>
            </div>
        @else
            <div style="display:flex; flex-direction:column; gap:16px;">
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
                                            <div class="avatar bg-primary text-white d-flex align-items-center justify-content-center" style="width:38px; height:38px; border-radius:50%; font-size:0.95rem;">{{ $cand->user->avatar }}</div>
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
        @endif
    @endif
</div>
@endsection
