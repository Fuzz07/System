<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#4f46e5">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="SSC Student">
    <title>{{ $pageTitle ?? 'SSC' }} — Student App</title>
    <meta name="description" content="SSC Transparency and Budget Allocation — Student Portal">
    <link rel="icon" type="image/png" href="{{ asset('assets/images/ssc_logo.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/icon-192.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('assets/css/mobile-student.css') }}" rel="stylesheet">
    @stack('head')
</head>

<body>

    {{-- ══════════════════════════════════════
    PWA INSTALL BANNER — Always visible
    Hides only if: already installed (standalone) or user dismissed
    ═══════════════════════════════════════ --}}
    <div class="install-banner" id="installBanner">
        <div class="install-banner-left">
            <div class="install-banner-icon">
                <img src="{{ asset('assets/img/icon-192.png') }}" alt="SSC" width="40" height="40"
                    onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                <div class="install-banner-icon-fallback" style="display:none;">SSC</div>
            </div>
            <div class="install-banner-text">
                <span class="install-banner-title">Install App</span>
                <span class="install-banner-sub">Add SSC Student to your home screen</span>
            </div>
        </div>
        <div class="install-banner-actions">
            <button class="install-banner-btn" id="installBtn" onclick="handleInstall()">
                <i class="bi bi-download"></i> Install
            </button>
            <button class="install-banner-dismiss" onclick="dismissBanner()" title="Dismiss">
                <i class="bi bi-x"></i>
            </button>
        </div>
    </div>

    {{-- iOS Install Instructions Modal --}}
    <div class="ios-modal-overlay" id="iosModal" onclick="closeIosModal()">
        <div class="ios-modal" onclick="event.stopPropagation()">
            <div class="ios-modal-handle"></div>
            <div style="text-align:center;padding:0 4px 4px;">
                <div style="font-size:1.5rem;margin-bottom:8px;">📲</div>
                <div style="font-size:1.1rem;font-weight:800;color:#0f172a;margin-bottom:6px;">Add to Home Screen</div>
                <div style="font-size:0.82rem;color:#64748b;margin-bottom:20px;line-height:1.5;">Follow these steps to
                    install the SSC Student app on your iPhone or iPad:</div>
            </div>
            <div class="ios-steps">
                <div class="ios-step">
                    <div class="ios-step-num">1</div>
                    <div class="ios-step-body">
                        <div class="ios-step-title">Tap the Share button</div>
                        <div class="ios-step-sub">Find <span class="ios-icon-badge"><i
                                    class="bi bi-box-arrow-up"></i></span> in Safari's bottom toolbar</div>
                    </div>
                </div>
                <div class="ios-step">
                    <div class="ios-step-num">2</div>
                    <div class="ios-step-body">
                        <div class="ios-step-title">Tap "Add to Home Screen"</div>
                        <div class="ios-step-sub">Scroll down in the share sheet and tap <strong>"Add to Home
                                Screen"</strong></div>
                    </div>
                </div>
                <div class="ios-step">
                    <div class="ios-step-num">3</div>
                    <div class="ios-step-body">
                        <div class="ios-step-title">Tap "Add" to confirm</div>
                        <div class="ios-step-sub">The SSC Student app will appear on your home screen</div>
                    </div>
                </div>
            </div>
            <div style="padding-top:8px;">
                <button onclick="closeIosModal()" class="ios-close-btn">Got it!</button>
            </div>
        </div>
    </div>

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
            <div class="app-bar-avatar" title="{{ Auth::user()->fullname }}">
                {{ Auth::user()->avatar }}
            </div>
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
            <a href="{{ route('mobile.student.officers') }}"
                class="nav-tab {{ request()->routeIs('mobile.student.officers') ? 'active' : '' }}" id="tab-officers">
                <div class="nav-tab-icon"><i
                        class="bi bi-people{{ request()->routeIs('mobile.student.officers') ? '-fill' : '' }}"></i>
                </div>
                <div class="nav-tab-label">Officers</div>
            </a>
            <a href="{{ route('mobile.student.feedback') }}"
                class="nav-tab {{ request()->routeIs('mobile.student.feedback') ? 'active' : '' }}" id="tab-feedback">
                <div class="nav-tab-icon"><i
                        class="bi bi-chat-dots{{ request()->routeIs('mobile.student.feedback') ? '-fill' : '' }}"></i>
                </div>
                <div class="nav-tab-label">Feedback</div>
            </a>
            <form method="POST" action="{{ route('logout') }}" style="flex:1;display:flex;">
                @csrf
                <button type="submit" class="nav-tab" id="tab-logout" title="Sign Out">
                    <div class="nav-tab-icon"><i class="bi bi-box-arrow-right"></i></div>
                    <div class="nav-tab-label">Sign Out</div>
                </button>
            </form>
        </nav>

    </div>{{-- /.mobile-app --}}

    {{-- Chatbot FAB --}}
    <a href="#chatbot" class="chatbot-fab" id="chatbotFab" title="Ask SSC Assistant" onclick="toggleChatbot(event)">
        <i class="bi bi-robot"></i>
    </a>

    {{-- Chatbot Panel --}}
    <div id="chatbotSheet"
        style="display:none;position:fixed;inset:0;z-index:600;background:rgba(15,23,42,0.5);backdrop-filter:blur(4px);align-items:flex-end;justify-content:center;">
        <div
            style="background:#fff;border-radius:28px 28px 0 0;width:100%;max-width:480px;max-height:88vh;display:flex;flex-direction:column;overflow:hidden;">
            <div
                style="padding:12px 20px 0;display:flex;justify-content:space-between;align-items:center;flex-shrink:0;">
                <div style="font-size:1rem;font-weight:700;color:#0f172a;">🤖 SSC Assistant</div>
                <button onclick="toggleChatbot(event)"
                    style="background:none;border:none;font-size:1.2rem;color:#94a3b8;cursor:pointer;padding:4px;">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div style="flex:1;overflow:auto;">
                @include('partials.chatbot')
            </div>
        </div>
    </div>

    @stack('modals')

    <script>
        // ── Device Detection ──
        const isIos = /iPhone|iPad|iPod/i.test(navigator.userAgent);
        const isStandalone = window.navigator.standalone === true || window.matchMedia('(display-mode: standalone)').matches;
        const DISMISSED_KEY = 'ssc_install_dismissed';

        // ── Show/hide banner on load ──
        (function initBanner() {
            const banner = document.getElementById('installBanner');
            if (!banner) return;

            // Hide if already running as installed app
            if (isStandalone) {
                banner.style.display = 'none';
                return;
            }

            // Hide if user previously dismissed
            if (localStorage.getItem(DISMISSED_KEY) === '1') {
                banner.style.display = 'none';
                return;
            }

            // Show banner — it's visible in HTML by default
            banner.style.display = 'flex';
        })();

        // ── Capture Android/Chrome install prompt ──
        let deferredPrompt = null;
        window.addEventListener('beforeinstallprompt', e => {
            e.preventDefault();
            deferredPrompt = e;
            // Already visible, nothing to show — just store the event
        });

        // ── Install button handler ──
        function handleInstall() {
            if (deferredPrompt) {
                // Android Chrome / Edge — native install prompt
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then(choice => {
                    if (choice.outcome === 'accepted') {
                        dismissBanner();
                    }
                    deferredPrompt = null;
                });
            } else if (isIos) {
                // iOS Safari — show step-by-step modal
                openIosModal();
            } else {
                // Fallback for other browsers (e.g. desktop Firefox) — show iOS-style instructions
                openIosModal();
            }
        }

        // ── Dismiss banner permanently ──
        function dismissBanner() {
            localStorage.setItem(DISMISSED_KEY, '1');
            const banner = document.getElementById('installBanner');
            if (banner) {
                banner.style.animation = 'bannerSlideOut 0.3s ease forwards';
                setTimeout(() => banner.style.display = 'none', 300);
            }
        }

        // ── iOS Modal ──
        function openIosModal() {
            const m = document.getElementById('iosModal');
            m.style.display = 'flex';
            requestAnimationFrame(() => m.classList.add('open'));
        }

        function closeIosModal() {
            const m = document.getElementById('iosModal');
            m.classList.remove('open');
            setTimeout(() => m.style.display = 'none', 300);
        }

        // ── Hide banner when app is installed ──
        window.addEventListener('appinstalled', dismissBanner);

        // ── Chatbot Toggle ──
        function toggleChatbot(e) {
            e.preventDefault();
            const sheet = document.getElementById('chatbotSheet');
            sheet.style.display = (sheet.style.display === 'none' || !sheet.style.display) ? 'flex' : 'none';
        }

        document.getElementById('chatbotSheet').addEventListener('click', function (e) {
            if (e.target === this) toggleChatbot(e);
        });

        // ── Register Service Worker ──
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => { }));
        }
    </script>

    @include('partials.logout-modal')
    @stack('scripts')
</body>

</html>