{{-- ══════════════════════════════════════
Notification Bell — Shared Partial
Sits beside the profile avatar in the mobile app bar and the desktop topbar.

Shows two things merged into one feed: the student's own notifications
(enrolment, elections, feedback replies) and every council announcement. The
FCM push is the separate, real-time channel; this is the inbox.

Pass a surface so announcement links point at the right shell:
  @include('partials.notification-bell')                        {{-- desktop --}}
  @include('partials.notification-bell', ['surface' => 'mobile'])
═══════════════════════════════════════ --}}

@if(Auth::check() && Auth::user()->isStudent())

@once
<style>
    .ssc-bell-wrap {
        position: relative;
        flex-shrink: 0;
    }

    .ssc-bell-btn {
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        border: none;
        background: rgba(0, 0, 0, 0.04);
        color: #475569;
        font-size: 1.15rem;
        cursor: pointer;
        position: relative;
        transition: background-color 0.2s ease, color 0.2s ease;
    }

    .ssc-bell-btn:hover {
        background: rgba(0, 0, 0, 0.08);
        color: #0f172a;
    }

    /* The count sits on the bell itself, so it has to escape the button box. */
    .ssc-bell-badge {
        position: absolute;
        top: -3px;
        right: -3px;
        min-width: 18px;
        height: 18px;
        padding: 0 5px;
        border-radius: 999px;
        background: #ef4444;
        color: #fff;
        font-size: 0.65rem;
        font-weight: 800;
        line-height: 18px;
        text-align: center;
        border: 2px solid #fff;
        box-sizing: content-box;
    }

    .ssc-bell-badge[hidden] {
        display: none;
    }

    /* Unread state: the bell fills in and gets a slow halo, so there is a
       visible signal even at a glance where the count is too small to read. */
    .ssc-bell-btn.has-unread {
        color: #e34f26;
        background: rgba(227, 79, 38, 0.1);
    }

    .ssc-bell-btn.has-unread::after {
        content: '';
        position: absolute;
        inset: -3px;
        border-radius: 14px;
        border: 2px solid rgba(227, 79, 38, 0.5);
        animation: sscBellHalo 2s ease-out infinite;
        pointer-events: none;
    }

    @keyframes sscBellHalo {
        0%   { transform: scale(0.9); opacity: 0.9; }
        70%  { transform: scale(1.18); opacity: 0; }
        100% { transform: scale(1.18); opacity: 0; }
    }

    .ssc-bell-btn.has-unread i {
        animation: sscBellSwing 2.6s ease-in-out infinite;
        transform-origin: top center;
    }

    @keyframes sscBellSwing {
        0%, 70%, 100% { transform: rotate(0deg); }
        75%           { transform: rotate(11deg); }
        80%           { transform: rotate(-9deg); }
        85%           { transform: rotate(6deg); }
        90%           { transform: rotate(-4deg); }
    }

    @media (prefers-reduced-motion: reduce) {
        .ssc-bell-btn.has-unread::after,
        .ssc-bell-btn.has-unread i { animation: none; }
    }

    /* Announcements carry a headline; personal notes are a single line. */
    .ssc-bell-title {
        font-size: 0.83rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.35;
        margin-bottom: 3px;
    }

    .ssc-bell-tag {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.6rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        padding: 2px 7px;
        border-radius: 999px;
        margin-bottom: 6px;
        background: rgba(227, 79, 38, 0.1);
        color: #c2410c;
    }

    .ssc-bell-tag.is-personal {
        background: rgba(59, 130, 246, 0.12);
        color: #1d4ed8;
    }

    .ssc-bell-panel {
        position: absolute;
        top: calc(100% + 10px);
        right: 0;
        width: min(330px, calc(100vw - 32px));
        max-height: 60vh;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.16);
        z-index: 1000;
        display: none;
        flex-direction: column;
        overflow: hidden;
    }

    .ssc-bell-panel.open {
        display: flex;
    }

    .ssc-bell-head {
        padding: 13px 16px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.85rem;
        font-weight: 800;
        color: #0f172a;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    .ssc-bell-list {
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        overscroll-behavior: contain;
    }

    .ssc-bell-item {
        display: block;
        padding: 12px 16px;
        border-bottom: 1px solid #f8fafc;
        text-decoration: none;
        color: inherit;
        background: #fff;
    }

    .ssc-bell-item:last-child {
        border-bottom: none;
    }

    .ssc-bell-item.is-unread {
        background: rgba(59, 130, 246, 0.06);
    }

    .ssc-bell-msg {
        font-size: 0.82rem;
        line-height: 1.45;
        color: #334155;
        font-weight: 600;
    }

    .ssc-bell-ago {
        font-size: 0.68rem;
        color: #94a3b8;
        margin-top: 4px;
    }

    .ssc-bell-empty {
        padding: 28px 16px;
        text-align: center;
        color: #94a3b8;
        font-size: 0.82rem;
    }

    .ssc-bell-empty i {
        font-size: 1.6rem;
        display: block;
        margin-bottom: 8px;
        opacity: 0.7;
    }
</style>
@endonce

<div class="ssc-bell-wrap" id="sscBellWrap" data-surface="{{ ($surface ?? 'desktop') }}">
    <button type="button" class="ssc-bell-btn" id="sscBellBtn"
        aria-label="Notifications" aria-haspopup="true" aria-expanded="false" aria-controls="sscBellPanel">
        <i class="bi bi-bell"></i>
        <span class="ssc-bell-badge" id="sscBellBadge" hidden>0</span>
    </button>

    <div class="ssc-bell-panel" id="sscBellPanel" role="menu" aria-label="Notifications">
        <div class="ssc-bell-head">
            <span>Notifications</span>
            <i class="bi bi-bell-fill" style="color:#cbd5e1;"></i>
        </div>
        <div class="ssc-bell-list" id="sscBellList">
            <div class="ssc-bell-empty">Loading…</div>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
    (function () {
        var wrap = document.getElementById('sscBellWrap');
        var btn = document.getElementById('sscBellBtn');
        var panel = document.getElementById('sscBellPanel');
        var badge = document.getElementById('sscBellBadge');
        var list = document.getElementById('sscBellList');
        if (!wrap || !btn || !panel) return;

        var SURFACE = wrap.dataset.surface || 'desktop';
        var URL_LIST = @json(route('student.notifications.index')) + '?surface=' + encodeURIComponent(SURFACE);
        var URL_COUNT = @json(route('student.notifications.unread'));
        var URL_READ = @json(route('student.notifications.read'));
        var csrfMeta = document.querySelector('meta[name="csrf-token"]');
        var CSRF = csrfMeta ? csrfMeta.getAttribute('content') : '';

        function setBadge(n) {
            var icon = btn.querySelector('i');
            if (n > 0) {
                badge.textContent = n > 99 ? '99+' : n;
                badge.hidden = false;
                btn.classList.add('has-unread');
                if (icon) icon.className = 'bi bi-bell-fill';
            } else {
                badge.hidden = true;
                btn.classList.remove('has-unread');
                if (icon) icon.className = 'bi bi-bell';
            }
        }

        function refreshCount() {
            fetch(URL_COUNT, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                .then(function (r) { return r.ok ? r.json() : null; })
                .then(function (d) { if (d) setBadge(d.unread || 0); })
                .catch(function () { /* offline is not worth surfacing on a badge */ });
        }

        function escapeHtml(s) {
            var d = document.createElement('div');
            d.textContent = s == null ? '' : s;
            return d.innerHTML;
        }

        function render(items) {
            if (!items || !items.length) {
                list.innerHTML = '<div class="ssc-bell-empty"><i class="bi bi-bell-slash"></i>'
                    + 'Nothing yet. Updates from the SSC land here.</div>';
                return;
            }
            list.innerHTML = items.map(function (n) {
                var cls = 'ssc-bell-item' + (n.unread ? ' is-unread' : '');
                var isAnn = n.kind === 'announcement';

                var inner = '<span class="ssc-bell-tag' + (isAnn ? '' : ' is-personal') + '">'
                    + '<i class="bi ' + (isAnn ? 'bi-megaphone-fill' : 'bi-person-check-fill') + '"></i>'
                    + (isAnn ? 'Announcement' : 'For you') + '</span>';

                if (n.title) {
                    inner += '<div class="ssc-bell-title">' + escapeHtml(n.title) + '</div>';
                }
                inner += '<div class="ssc-bell-msg">' + escapeHtml(n.message) + '</div>'
                    + '<div class="ssc-bell-ago">' + escapeHtml(n.ago) + '</div>';

                return n.url
                    ? '<a class="' + cls + '" href="' + escapeHtml(n.url) + '">' + inner + '</a>'
                    : '<div class="' + cls + '">' + inner + '</div>';
            }).join('');
        }

        function open() {
            panel.classList.add('open');
            btn.setAttribute('aria-expanded', 'true');
            list.innerHTML = '<div class="ssc-bell-empty">Loading…</div>';

            fetch(URL_LIST, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                .then(function (r) { return r.ok ? r.json() : []; })
                .then(render)
                .catch(function () {
                    list.innerHTML = '<div class="ssc-bell-empty"><i class="bi bi-wifi-off"></i>'
                        + 'Could not load notifications.</div>';
                });

            // Opening the bell is what "I have seen these" means here, so the
            // badge clears straight away rather than waiting on the round trip.
            setBadge(0);
            fetch(URL_READ, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                credentials: 'same-origin'
            }).catch(function () { refreshCount(); });
        }

        function close() {
            panel.classList.remove('open');
            btn.setAttribute('aria-expanded', 'false');
        }

        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            if (panel.classList.contains('open')) {
                close();
            } else {
                open();
            }
        });

        document.addEventListener('click', function (e) {
            if (!wrap.contains(e.target)) close();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') close();
        });

        refreshCount();
        setInterval(refreshCount, 60000);
    })();
</script>
@endpush
@endonce

@endif
