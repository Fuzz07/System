@php
    $pageTitle = 'Announcements';
    $pageSubtitle = 'From Your Student Council';
@endphp
@extends('layouts.mobile-student')

@section('content')

    <div class="hero-banner" style="background: linear-gradient(135deg, var(--indigo-600) 0%, var(--indigo-800) 100%);">
        <div class="hero-banner-title" style="display:flex; align-items:center; gap:8px;">
            <i class="bi bi-megaphone-fill" style="font-size: 1.5rem; color: #fff;"></i> News &amp; Updates
        </div>
        <div class="hero-banner-sub" style="color: rgba(255,255,255,0.85);">Official updates from the SSC</div>
    </div>

    @forelse($announcements as $a)
        {{-- Announcement Card --}}
        <div class="ann-card ripple" onclick="openAnn({{ $a->id }})" style="margin: 16px; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.02); overflow: hidden; background: #fff;">
            <div style="padding: 20px;">
                <div style="display: flex; gap: 12px; align-items: flex-start; margin-bottom: 12px;">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(79,70,229,0.1); display: flex; align-items: center; justify-content: center; color: var(--indigo-600); font-size: 1.25rem; flex-shrink: 0;">
                        <i class="bi bi-info-circle-fill"></i>
                    </div>
                    <div>
                        <div style="font-size: 1.1rem; font-weight: 700; color: #1e293b; line-height: 1.3;">{{ $a->title }}</div>
                        <div style="font-size: 0.75rem; color: #64748b; margin-top: 4px;">
                            {{ $a->created_at?->format('M d, Y') }} &bull; {{ $a->created_at?->diffForHumans() }}
                        </div>
                    </div>
                </div>
                
                <div style="font-size: 0.9rem; color: #475569; line-height: 1.6; margin-bottom: 16px;">
                    {{ Str::limit($a->content, 120) }}
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 12px; border-top: 1px solid #f1f5f9;">
                    <div style="font-size: 0.8rem; color: #64748b; font-weight: 500;">
                        <i class="bi bi-person-circle"></i> {{ $a->author->fullname ?? 'SSC Admin' }}
                    </div>
                    <div style="font-size: 0.85rem; font-weight: 600; color: var(--indigo-600);">
                        Read More <i class="bi bi-arrow-right"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bottom Sheet for this announcement --}}
        <div class="ann-sheet-overlay" id="annOverlay{{ $a->id }}" onclick="closeAnn({{ $a->id }})" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.4); z-index: 1000; align-items: flex-end;">
            <div class="ann-sheet" onclick="event.stopPropagation()" style="background: #fff; width: 100%; border-radius: 24px 24px 0 0; padding: 24px; max-height: 90vh; overflow-y: auto; transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); transform: translateY(100%);">
                <div style="width: 40px; height: 5px; background: #cbd5e1; border-radius: 4px; margin: 0 auto 24px;"></div>
                
                <div style="text-align:center;margin-bottom:24px;">
                    <div style="width:64px;height:64px;border-radius:20px;background:rgba(79,70,229,0.1);color:var(--primary);font-size:1.75rem;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                        <i class="bi bi-megaphone-fill"></i>
                    </div>
                    <div style="font-size:1.25rem;font-weight:800;color:#0f172a;line-height:1.4;margin-bottom:8px;">
                        {{ $a->title }}</div>
                    <div style="font-size:0.85rem;color:#64748b; font-weight: 500;">
                        {{ $a->author->fullname ?? 'SSC Admin' }}
                        &nbsp;·&nbsp;
                        {{ $a->created_at?->format('F d, Y') }}
                    </div>
                </div>

                <div style="font-size:0.95rem;line-height:1.8;color:#334155; margin-bottom: 24px;">
                    {!! nl2br(e($a->content)) !!}
                </div>

                {{-- Proof --}}
                @if($a->project_id && $a->proposal?->completion_proof)
                    <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 12px; padding: 16px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                        <div>
                            <div style="color: #059669; font-weight: 700; font-size: 0.9rem; margin-bottom: 4px;"><i class="bi bi-shield-check"></i> Verified Proof</div>
                            <div style="color: #047857; font-size: 0.8rem; opacity: 0.8;">Official receipt is available.</div>
                        </div>
                        <a href="{{ asset('storage/' . $a->proposal->completion_proof) }}" target="_blank" style="background: #10b981; color: white; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 0.85rem; text-decoration: none;">
                            <i class="bi bi-receipt"></i> View
                        </a>
                    </div>
                @endif

                <button onclick="closeAnn({{ $a->id }})" style="width: 100%; background: #f1f5f9; color: #475569; border: none; padding: 16px; border-radius: 12px; font-weight: 600; font-size: 1rem;">
                    Close
                </button>
            </div>
        </div>

    @empty
        <div style="text-align: center; padding: 60px 20px;">
            <div style="width: 80px; height: 80px; background: #f1f5f9; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: #94a3b8; margin: 0 auto 20px;">
                <i class="bi bi-megaphone"></i>
            </div>
            <div style="font-size: 1.1rem; font-weight: 700; color: #1e293b; margin-bottom: 8px;">No Announcements</div>
            <div style="font-size: 0.9rem; color: #64748b;">Check back later for SSC updates.</div>
        </div>
    @endforelse
@endsection

@push('scripts')
    <script>
        function openAnn(id) {
            const overlay = document.getElementById('annOverlay' + id);
            if (!overlay) return;
            overlay.style.display = 'flex';
            const sheet = overlay.querySelector('.ann-sheet');
            // Force reflow
            void sheet.offsetWidth;
            sheet.style.transform = 'translateY(0)';
            document.body.style.overflow = 'hidden';
        }

        function closeAnn(id) {
            const overlay = document.getElementById('annOverlay' + id);
            if (!overlay) return;
            const sheet = overlay.querySelector('.ann-sheet');
            sheet.style.transform = 'translateY(100%)';
            setTimeout(() => {
                overlay.style.display = 'none';
                document.body.style.overflow = '';
            }, 300);
        }
    </script>
@endpush