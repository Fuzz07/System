@php
    $pageTitle = 'Announcements';
    $pageSubtitle = 'From Your Student Council';
@endphp
@extends('layouts.mobile-student')

@section('content')

    <div class="hero-banner" style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);">
        <div class="hero-banner-title" style="display:flex; align-items:center; gap:8px;">
            <i class="bi bi-megaphone-fill" style="font-size: 1.5rem; color: #fff;"></i> News &amp; Updates
        </div>
        <div class="hero-banner-sub" style="color: rgba(255,255,255,0.85);">Official updates from the SSC</div>
    </div>

    <div style="display: flex; gap: 8px; padding: 0 16px 4px; overflow-x: auto;">
        <a href="{{ route('mobile.student.announcements') }}" style="flex-shrink:0; text-decoration:none; font-size:0.8rem; font-weight:600; padding:7px 16px; border-radius:20px; {{ !$category ? 'background:var(--primary); color:#fff;' : 'background:#f1f5f9; color:#475569;' }}">All</a>
        @foreach(\App\Models\Announcement::CATEGORIES as $value => $label)
        <a href="{{ route('mobile.student.announcements', ['category' => $value]) }}" style="flex-shrink:0; text-decoration:none; font-size:0.8rem; font-weight:600; padding:7px 16px; border-radius:20px; {{ $category === $value ? 'background:var(--primary); color:#fff;' : 'background:#f1f5f9; color:#475569;' }}">{{ $label }}</a>
        @endforeach
    </div>

    @forelse($announcements as $a)
        {{-- Announcement Card --}}
        <div class="ann-card ripple" onclick="openAnn({{ $a->id }})" style="margin: 16px; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.02); overflow: hidden; background: #fff;">
            @if($a->image_path)
            <img src="{{ \App\Helpers\SscHelper::getUploadUrl($a->image_path) }}" alt="" style="width: 100%; height: 160px; object-fit: cover; display: block;">
            @endif
            <div style="padding: 20px;">
                <div style="display: flex; gap: 12px; align-items: flex-start; margin-bottom: 12px;">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(var(--primary-rgb), 0.1); display: flex; align-items: center; justify-content: center; color: var(--primary); font-size: 1.25rem; flex-shrink: 0;">
                        <i class="bi bi-info-circle-fill"></i>
                    </div>
                    <div>
                        <span style="display:inline-block; font-size: 0.65rem; text-transform:uppercase; font-weight:700; padding:2px 8px; border-radius:8px; margin-bottom:4px; {{ $a->category === 'lost_item' ? 'background:#fef3c7; color:#92400e;' : 'background:#e0f2fe; color:#075985;' }}">{{ $a->category_label }}</span>
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
                    <div style="font-size: 0.8rem; color: #64748b; font-weight: 500; display:flex; align-items:center; gap:10px;">
                        <span><i class="bi bi-person-circle"></i> {{ $a->author->fullname ?? 'SSC Admin' }}</span>
                        @if($a->category === 'lost_item')
                        <span><i class="bi bi-chat-dots"></i> {{ $a->comments->count() }}</span>
                        @endif
                    </div>
                    <div style="font-size: 0.85rem; font-weight: 600; color: var(--primary);">
                        Read More <i class="bi bi-arrow-right"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bottom Sheet for this announcement --}}
        <div class="ann-sheet-overlay" id="annOverlay{{ $a->id }}" onclick="closeAnn({{ $a->id }})"
            role="dialog" aria-modal="true" aria-labelledby="annTitle{{ $a->id }}">
            <div class="ann-sheet" onclick="event.stopPropagation()">
                <div class="ann-sheet-handle"></div>

                {{-- Sticky header: identity stays visible while the body scrolls --}}
                <div class="ann-sheet-head">
                    <div class="ann-sheet-head-icon {{ $a->category === 'lost_item' ? 'lost' : '' }}">
                        <i class="bi {{ $a->category === 'lost_item' ? 'bi-search' : 'bi-megaphone-fill' }}"></i>
                    </div>
                    <div style="flex:1; min-width:0;">
                        <div class="ann-sheet-title" id="annTitle{{ $a->id }}">{{ $a->title }}</div>
                        <div class="ann-sheet-meta">
                            <span>{{ $a->author->fullname ?? 'SSC Admin' }}</span>
                            <span>·</span>
                            <span>{{ $a->created_at?->format('M d, Y') }}</span>
                        </div>
                    </div>
                    <button type="button" class="ann-sheet-close" onclick="closeAnn({{ $a->id }})" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="ann-sheet-body">
                    @if($a->image_path)
                        <img src="{{ \App\Helpers\SscHelper::getUploadUrl($a->image_path) }}" alt=""
                            class="ann-sheet-hero" style="margin-top:18px;">
                    @endif

                    <div class="ann-sheet-text" style="{{ $a->image_path ? '' : 'margin-top:18px;' }}">{{ $a->content }}</div>

                    {{-- Proof --}}
                    @if($a->project_id && $a->proposal?->completion_proof)
                        <div class="ann-proof">
                            <div>
                                <div class="ann-proof-title"><i class="bi bi-shield-check"></i> Verified Proof</div>
                                <div class="ann-proof-sub">Official receipt is available.</div>
                            </div>
                            <a href="{{ \App\Helpers\SscHelper::getUploadUrl($a->proposal->completion_proof) }}"
                                target="_blank" rel="noopener" class="ann-proof-btn">
                                <i class="bi bi-receipt"></i> View
                            </a>
                        </div>
                    @endif

                    {{-- Comments (Lost & Found only) --}}
                    @if($a->category === 'lost_item')
                        <div class="ann-comments">
                            <div class="ann-comments-title">
                                <i class="bi bi-chat-dots"></i> Comments ({{ $a->comments->count() }})
                            </div>

                            <form method="POST" action="{{ route('mobile.student.announcements.comment', $a) }}"
                                class="ann-comment-form">
                                @csrf
                                <textarea name="comment" rows="2" required
                                    placeholder="Found this item, or know whose it is?"></textarea>
                                <button type="submit" class="ann-comment-submit">
                                    Post Comment <i class="bi bi-send"></i>
                                </button>
                            </form>

                            @forelse($a->comments as $c)
                                <div class="ann-comment">
                                    <div class="ann-comment-avatar"><i class="bi bi-person-circle"></i></div>
                                    <div style="flex:1; min-width:0;">
                                        <div class="ann-comment-head">
                                            <span class="ann-comment-author">
                                                <i class="bi bi-person-fill-lock"></i> Anonymous Student
                                            </span>
                                            <span class="ann-comment-time">{{ $c->created_at?->diffForHumans() }}</span>
                                        </div>
                                        <div class="ann-comment-bubble">{!! nl2br(e($c->comment)) !!}</div>
                                    </div>
                                </div>
                            @empty
                                <div class="ann-comments-empty">No comments yet. Be the first to help!</div>
                            @endforelse
                        </div>
                    @endif
                </div>
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
        // The sheet's motion lives in CSS (.ann-sheet-overlay.open); JS only
        // toggles the class, so the backdrop fade and the slide stay in sync.
        function openAnn(id) {
            const overlay = document.getElementById('annOverlay' + id);
            if (!overlay) return;
            overlay.style.display = 'flex';
            void overlay.offsetWidth;          // force reflow so the transition runs
            overlay.classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        function closeAnn(id) {
            const overlay = document.getElementById('annOverlay' + id);
            if (!overlay || !overlay.classList.contains('open')) return;
            overlay.classList.remove('open');
            document.body.style.overflow = '';
            setTimeout(() => { overlay.style.display = 'none'; }, 350);
        }

        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape') return;
            const open = document.querySelector('.ann-sheet-overlay.open');
            if (open) closeAnn(open.id.replace('annOverlay', ''));
        });

        // Drag the sheet down to dismiss, the way a native sheet behaves.
        document.querySelectorAll('.ann-sheet').forEach(function (sheet) {
            const overlay = sheet.closest('.ann-sheet-overlay');
            const body = sheet.querySelector('.ann-sheet-body');
            let startY = null;

            sheet.addEventListener('touchstart', function (e) {
                // Only start a drag when the content is scrolled to the top,
                // otherwise this would fight with scrolling the article.
                if (body && body.scrollTop > 0) { startY = null; return; }
                startY = e.touches[0].clientY;
            }, { passive: true });

            sheet.addEventListener('touchmove', function (e) {
                if (startY === null) return;
                const dy = e.touches[0].clientY - startY;
                if (dy > 0) sheet.style.transform = 'translateY(' + dy + 'px)';
            }, { passive: true });

            sheet.addEventListener('touchend', function (e) {
                if (startY === null) return;
                const dy = e.changedTouches[0].clientY - startY;
                sheet.style.transform = '';
                if (dy > 90) closeAnn(overlay.id.replace('annOverlay', ''));
                startY = null;
            });
        });
    </script>
@endpush