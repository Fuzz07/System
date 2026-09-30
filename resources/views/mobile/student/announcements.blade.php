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
        {{-- Announcement Card: photo on top, caption below --}}
        <div class="ann-card ripple {{ $a->category === 'lost_item' ? 'category-lost' : '' }}" role="button" tabindex="0"
            onclick="openAnn({{ $a->id }})"
            onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); openAnn({{ $a->id }}); }">
            <div class="ann-card-media">
                @if($a->image_path)
                <img src="{{ \App\Helpers\SscHelper::getUploadUrl($a->image_path) }}" alt="" loading="lazy" decoding="async">
                @else
                <div class="ann-card-placeholder"><i class="bi {{ $a->category === 'lost_item' ? 'bi-search' : 'bi-megaphone-fill' }}"></i></div>
                @endif
                <span class="ann-card-badge">
                    <i class="bi {{ $a->category === 'lost_item' ? 'bi-search' : 'bi-megaphone' }}"></i> {{ $a->category_label }}
                </span>
            </div>
            <div class="ann-card-caption">
                <div class="ann-card-title">{{ $a->title }}</div>
                <div class="ann-card-text">{{ Str::limit($a->content, 160) }}</div>
                <div class="ann-card-meta">
                    <span><i class="bi bi-person-circle"></i> {{ $a->author->fullname ?? 'SSC Admin' }}</span>
                    <span><i class="bi bi-clock"></i> {{ $a->created_at?->diffForHumans() }}</span>
                    <span><i class="bi bi-chat-dots"></i> {{ $a->comments->count() }}</span>
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
                        <img src="{{ \App\Helpers\SscHelper::getUploadUrl($a->image_path) }}" alt="" loading="lazy" decoding="async"
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

                    {{-- Comments --}}
                    <div class="ann-comments">
                        <div class="ann-comments-title">
                            <i class="bi bi-chat-dots"></i> Comments ({{ $a->comments->count() }})
                        </div>

                        <form method="POST" action="{{ route('mobile.student.announcements.comment', $a) }}"
                            class="ann-comment-form">
                            @csrf
                            <textarea name="comment" rows="2" required
                                placeholder="{{ $a->category === 'lost_item' ? 'Found this item, or know whose it is?' : 'Share your thoughts or feedback...' }}"></textarea>
                            <button type="submit" class="ann-comment-submit">
                                Post Comment <i class="bi bi-send"></i>
                            </button>
                        </form>

                        @forelse($a->comments as $c)
                            <div class="ann-comment">
                                <div class="ann-comment-avatar"><i class="bi bi-person-circle"></i></div>
                                <div style="flex:1; min-width:0;" data-own-comment>
                                    <div class="ann-comment-head">
                                        <span class="ann-comment-author">
                                            <i class="bi bi-person-fill-lock"></i> Anonymous Student
                                        </span>
                                        <span class="ann-comment-time">{{ $c->created_at?->diffForHumans() }}</span>
                                    </div>
                                    <div class="ann-comment-bubble" data-comment-text>{!! nl2br(e($c->comment)) !!}</div>
                                    @include('partials.own-comment-actions', ['comment' => $c, 'parent' => $a, 'routes' => 'mobile.student.announcements.comments'])
                                </div>
                            </div>
                        @empty
                            <div class="ann-comments-empty">{{ $a->category === 'lost_item' ? 'No comments yet. Be the first to help!' : 'No comments yet. Be the first to share your thoughts!' }}</div>
                        @endforelse
                    </div>
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