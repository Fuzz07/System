<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.security-guard')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#e34f26">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="SSC Student">
    @php
        $__activeSyLayout = \App\Models\SchoolYear::where('is_active', 1)->first();
        $__votingOpen = $__activeSyLayout && $__activeSyLayout->voting_open;
        $__hasApprovedCandidates = $__activeSyLayout
            ? \App\Models\Candidacy::where('school_year', $__activeSyLayout->label)->where('status', 'approved')->exists()
            : false;
        $__showVoteTab = $__votingOpen && $__hasApprovedCandidates;
    @endphp
    <title>{{ $pageTitle ?? 'SSC' }} — Student App</title>
    <meta name="description" content="SSC Transparency and Budget Allocation — Student Portal">
    <link rel="icon" type="image/png" href="{{ asset('assets/images/ssc_logo.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/icon-192.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('assets/css/mobile-student.css') }}?v={{ @filemtime(public_path('assets/css/mobile-student.css')) ?: 1 }}" rel="stylesheet">
    @stack('head')
</head>

<body>

    @include('partials.pwa-installer', ['floating' => false])

    {{-- ══════ App Shell ══════ --}}
    <div class="mobile-app" id="mobileApp">

        {{-- Top App Bar --}}
        <header class="app-bar {{ $appBarClass ?? '' }}" id="appBar">
            @if(isset($showBack) && $showBack)
                <a href="{{ $backUrl ?? 'javascript:history.back()' }}" class="app-bar-back">
                    <i class="bi bi-chevron-left"></i>
                </a>
            @endif
            <div class="app-bar-titles">
                <div class="app-bar-title">{{ $pageTitle ?? 'SSC Student' }}</div>
                @if(isset($pageSubtitle))
                    <div class="app-bar-sub">{{ $pageSubtitle }}</div>
                @endif
            </div>
            @yield('app-bar-right')
            <button type="button" class="app-bar-avatar" id="accountTrigger"
                aria-label="Account menu" aria-haspopup="dialog" aria-expanded="false" aria-controls="accountSheet"
                style="border:none; cursor:pointer;">
                {{ Auth::user()->avatar }}
            </button>
        </header>

        {{-- Flash Messages --}}
        @if(session('success') || session('danger') || session('warning') || $errors->any())
            <div style="padding: 12px 16px 0;">
                @if(session('success'))
                    <div class="m-alert m-alert-success"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
                @endif
                @if(session('danger'))
                    <div class="m-alert m-alert-danger"><i class="bi bi-exclamation-circle-fill"></i> {{ session('danger') }}
                    </div>
                @endif
                @if(session('warning'))
                    <div class="m-alert m-alert-warning"><i class="bi bi-exclamation-triangle-fill"></i>
                        {{ session('warning') }}</div>
                @endif
                @if($errors->any())
                    <div class="m-alert m-alert-danger">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif
            </div>
        @endif

        {{-- Main Page Content --}}
        <main class="page-content {{ $contentClass ?? '' }}" id="pageContent">
            @yield('content')
        </main>

        {{-- Bottom Navigation --}}
        <nav class="bottom-nav">
            <a href="{{ route('mobile.student.proposals') }}"
                class="nav-tab {{ request()->routeIs('mobile.student.proposals', 'mobile.student.proposal.show') ? 'active' : '' }}"
                id="tab-projects">
                <div class="nav-tab-icon"><i
                        class="bi bi-lightbulb{{ request()->routeIs('mobile.student.proposals', 'mobile.student.proposal.show') ? '-fill' : '' }}"></i>
                </div>
                <div class="nav-tab-label">Projects</div>
            </a>
            <a href="{{ route('mobile.student.announcements') }}"
                class="nav-tab {{ request()->routeIs('mobile.student.announcements') ? 'active' : '' }}" id="tab-ann">
                <div class="nav-tab-icon"><i
                        class="bi bi-megaphone{{ request()->routeIs('mobile.student.announcements') ? '-fill' : '' }}"></i>
                </div>
                <div class="nav-tab-label">News</div>
            </a>

            {{-- Voting Tab — only visible when elections are live --}}
            @if($__showVoteTab)
            <a href="{{ route('mobile.student.voting') }}"
                class="nav-tab {{ request()->routeIs('mobile.student.voting') ? 'active' : '' }}"
                id="tab-vote"
                style="position: relative;">
                <div class="nav-tab-icon" style="position: relative;">
                    <i class="bi bi-check-square{{ request()->routeIs('mobile.student.voting') ? '-fill' : '' }}"></i>
                    {{-- Live pulse badge --}}
                    <span style="position: absolute; top: -2px; right: -4px; width: 8px; height: 8px; background: #ef4444; border-radius: 50%; border: 1.5px solid #fff; animation: navLivePulse 1.5s infinite;"></span>
                </div>
                <div class="nav-tab-label" style="color: #ef4444; font-weight: 800;">Vote!</div>
            </a>
            @endif

            <a href="{{ route('mobile.student.officers') }}"
                class="nav-tab {{ request()->routeIs('mobile.student.officers') ? 'active' : '' }}" id="tab-officers">
                <div class="nav-tab-icon"><i
                        class="bi bi-people{{ request()->routeIs('mobile.student.officers') ? '-fill' : '' }}"></i>
                </div>
                <div class="nav-tab-label">Officers</div>
            </a>
            <a href="{{ route('mobile.student.enrollment') }}"
                class="nav-tab {{ request()->routeIs('mobile.student.enrollment') ? 'active' : '' }}" id="tab-enrollment">
                <div class="nav-tab-icon"><i
                        class="bi bi-credit-card{{ request()->routeIs('mobile.student.enrollment') ? '-fill' : '' }}"></i>
                </div>
                <div class="nav-tab-label">Enroll</div>
            </a>
        </nav>

        {{-- Account Sheet — secondary actions moved out of the tab bar --}}
        <div class="sheet-overlay" id="sheetOverlay"></div>
        <div class="account-sheet" id="accountSheet" role="dialog" aria-modal="true" aria-label="Account">
            <div class="sheet-handle"></div>
            <div class="sheet-profile">
                <div class="sheet-avatar">{{ Auth::user()->avatar }}</div>
                <div style="min-width:0;">
                    <div class="sheet-name">{{ Auth::user()->fullname }}</div>
                    <div class="sheet-email">{{ Auth::user()->email }}</div>
                </div>
            </div>
            <a href="{{ route('mobile.student.feedback') }}" class="sheet-item">
                <i class="bi bi-chat-dots"></i> Send Feedback
                <i class="bi bi-chevron-right sheet-chevron"></i>
            </a>
            <form method="POST" action="{{ url('/logout') }}">
                @csrf
                <button type="submit" class="sheet-item danger logout-btn">
                    <i class="bi bi-box-arrow-right"></i> Sign Out
                </button>
            </form>
        </div>

        <style>
            @keyframes navLivePulse {
                0%, 100% { transform: scale(1); opacity: 1; }
                50% { transform: scale(1.4); opacity: 0.6; }
            }
        </style>

    </div>{{-- /.mobile-app --}}

    @include('partials.chatbot')

    {{-- The chatbot partial is shared with the desktop portal and ships its own navy
         palette. Re-point its tokens at the app brand for the mobile shell only.
         This must come after the include so it wins on document order. --}}
    <style>
        :root {
            --chatbot-primary: #e34f26;
            --chatbot-primary-dark: #d13f19;
            --chatbot-gradient: linear-gradient(135deg, #f06529 0%, #d13f19 100%);
        }
    </style>

    @stack('modals')
    @include('partials.logout-modal')
    @stack('scripts')


    <script>
        // Account sheet — opened from the app-bar avatar.
        (function () {
            var trigger = document.getElementById('accountTrigger');
            var sheet = document.getElementById('accountSheet');
            var overlay = document.getElementById('sheetOverlay');
            if (!trigger || !sheet || !overlay) return;

            function open() {
                sheet.classList.add('show');
                overlay.classList.add('show');
                trigger.setAttribute('aria-expanded', 'true');
            }

            function close() {
                sheet.classList.remove('show');
                overlay.classList.remove('show');
                trigger.setAttribute('aria-expanded', 'false');
            }

            trigger.addEventListener('click', open);
            overlay.addEventListener('click', close);
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') close();
            });

            // Swipe the sheet down to dismiss, the way a native sheet behaves.
            var startY = null;
            sheet.addEventListener('touchstart', function (e) { startY = e.touches[0].clientY; }, { passive: true });
            sheet.addEventListener('touchmove', function (e) {
                if (startY === null) return;
                var dy = e.touches[0].clientY - startY;
                if (dy > 0) sheet.style.transform = 'translateX(-50%) translateY(' + dy + 'px)';
            }, { passive: true });
            sheet.addEventListener('touchend', function (e) {
                if (startY === null) return;
                var dy = e.changedTouches[0].clientY - startY;
                sheet.style.transform = '';
                if (dy > 80) close();
                startY = null;
            });
        })();
    </script>

    <script>
        // Automatic FCM Token Registration via Android Bridge
        (function() {
            try {
                var fcmToken = null;
                if (window.AndroidBridge && typeof window.AndroidBridge.getFcmToken === 'function') {
                    fcmToken = window.AndroidBridge.getFcmToken();
                }
                if (fcmToken && fcmToken.length > 10) {
                    var lastSentToken = sessionStorage.getItem('sent_fcm_token');
                    if (lastSentToken !== fcmToken) {
                        fetch('{{ route("student.api.device_token") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                fcm_token: fcmToken,
                                device_type: 'android',
                                device_name: 'SSC Mobile App'
                            })
                        }).then(function(res) {
                            return res.json();
                        }).then(function(data) {
                            sessionStorage.setItem('sent_fcm_token', fcmToken);
                            console.log('FCM Device Token registered:', data);
                        }).catch(function(err) {
                            console.error('FCM Token registration error:', err);
                        });
                    }
                }
            } catch (e) {
                console.error('FCM Bridge error:', e);
            }
        })();
    </script>
    <script src="{{ asset('assets/js/main.js') }}?v=1.0.3"></script>
</body>

</html>
