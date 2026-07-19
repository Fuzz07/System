@php
    $pageTitle = 'Announcements';
    $pageSubtitle = 'From Your Student Council';
@endphp
@extends('layouts.mobile-student')

@section('content')

    <div class="hero-banner">
        <div class="hero-banner-title">📢 Announcements</div>
        <div class="hero-banner-sub">Official updates from the SSC</div>
    </div>

    @forelse($announcements as $a)
        {{-- Announcement Card --}}
        <div class="ann-card ripple" onclick="openAnn({{ $a->id }})">
            <div class="ann-timeline">
                <div class="ann-dot"><i class="bi bi-megaphone-fill"></i></div>
                <div class="ann-body">
                    <div class="ann-date">
                        <i class="bi bi-calendar3"></i> {{ $a->created_at?->format('M d, Y') }}
                        &nbsp;·&nbsp;
                        {{ $a->created_at?->diffForHumans() }}
                    </div>
                    <div class="ann-title">{{ $a->title }}</div>
                    <div class="ann-excerpt">{{ Str::limit($a->content, 120) }}</div>
                </div>
            </div>
            <div class="ann-footer">
                <div style="font-size:0.75rem;color:var(--slate-400);">
                    <i class="bi bi-person-circle"></i> {{ $a->author->fullname ?? 'SSC Admin' }}
                </div>
                <div style="font-size:0.78rem;font-weight:600;color:var(--primary);">
                    Read full <i class="bi bi-chevron-right"></i>
                </div>
            </div>
        </div>

        {{-- Bottom Sheet for this announcement --}}
        <div class="ann-sheet-overlay" id="annOverlay{{ $a->id }}" onclick="closeAnn({{ $a->id }})" style="display: none;">
            <div class="ann-sheet" onclick="event.stopPropagation()">
                <div class="ann-sheet-handle"></div>
                <div class="ann-sheet-body">
                    {{-- Header --}}
                    <div style="text-align:center;margin-bottom:20px;">
                        <div
                            style="width:56px;height:56px;border-radius:16px;background:rgba(79,70,229,0.1);color:var(--primary);font-size:1.5rem;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                            <i class="bi bi-megaphone-fill"></i>
                        </div>
                        <div style="font-size:1.1rem;font-weight:800;color:#0f172a;line-height:1.3;margin-bottom:8px;">
                            {{ $a->title }}</div>
                        <div style="font-size:0.75rem;color:#94a3b8;">
                            <i class="bi bi-person-circle"></i> {{ $a->author->fullname ?? 'SSC Admin' }}
                            &nbsp;·&nbsp;
                            <i class="bi bi-calendar3"></i> {{ $a->created_at?->format('F d, Y') }}
                        </div>
                    </div>

                    <hr style="border-color:#f1f5f9;margin-bottom:20px;">

                    {{-- Content --}}
                    <div style="font-size:0.95rem;line-height:1.85;color:#334155;">
                        {!! nl2br(e($a->content)) !!}
                    </div>

                    {{-- Proof --}}
                    @if($a->project_id && $a->proposal?->completion_proof)
                        <div class="proof-banner" style="margin-top:20px;">
                            <div>
                                <div class="proof-label"><i class="bi bi-shield-check"></i> Verified Audit Proof</div>
                                <div class="proof-sub">Official receipt is available.</div>
                            </div>
                            <a href="{{ asset('storage/' . $a->proposal->completion_proof) }}" target="_blank" class="proof-btn">
                                <i class="bi bi-receipt"></i> View
                            </a>
                        </div>
                    @endif

                    <button onclick="closeAnn({{ $a->id }})" class="m-btn m-btn-secondary m-btn-block" style="margin-top:20px;">
                        Close
                    </button>
                </div>
            </div>
        </div>

    @empty
        <div class="empty-state">
            <i class="bi bi-megaphone"></i>
            <div class="empty-state-title">No Announcements</div>
            <div class="empty-state-sub">Check back later for SSC updates.</div>
        </div>
    @endforelse
@endsection

@push('scripts')
    <script>
        function openAnn(id) {
            const overlay = document.getElementById('annOverlay' + id);
            if (!overlay) return;
            overlay.style.display = 'flex';
            requestAnimationFrame(() => overlay.classList.add('open'));
            document.body.style.overflow = 'hidden';
        }

        function closeAnn(id) {
            const overlay = document.getElementById('annOverlay' + id);
            if (!overlay) return;
            overlay.classList.remove('open');
            setTimeout(() => {
                overlay.style.display = 'none';
                document.body.style.overflow = '';
            }, 300);
        }
    </script>
@endpush