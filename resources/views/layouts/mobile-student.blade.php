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
        // ── Chatbot Toggle ──
        function toggleChatbot(e) {
            e.preventDefault();
            const sheet = document.getElementById('chatbotSheet');
            sheet.style.display = (sheet.style.display === 'none' || !sheet.style.display) ? 'flex' : 'none';
        }

        document.getElementById('chatbotSheet').addEventListener('click', function (e) {
            if (e.target === this) toggleChatbot(e);
        });
    </script>

    @include('partials.logout-modal')
    @stack('scripts')
</body>

</html>