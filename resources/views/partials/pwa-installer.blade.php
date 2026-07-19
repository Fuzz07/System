{{-- ══════════════════════════════════════
PWA INSTALL BANNER — Shared Partial Component
Can be included anywhere. Supports standard top-banner style or floating bottom-card style.
Usage: 
  @include('partials.pwa-installer', ['floating' => true])
═══════════════════════════════════════ --}}

@php
    $floating = $floating ?? false;
@endphp

@once
    {{-- CSS Styles for PWA Banner and iOS Modal --}}
    <style>
        .pwa-installer-banner {
            display: none;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 16px;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: #fff;
            z-index: 9999;
            width: 100%;
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            box-sizing: border-box;
        }

        .pwa-installer-banner * {
            box-sizing: border-box;
        }

        /* Floating style for landing page and auth pages */
        .pwa-installer-banner.floating {
            position: fixed;
            bottom: 16px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            max-width: 480px;
            width: calc(100% - 32px);
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(79, 70, 229, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.2);
            opacity: 0;
            transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.4s ease;
        }

        .pwa-installer-banner.floating.show {
            display: flex;
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }

        /* Top banner inline style for dashboard */
        .pwa-installer-banner.inline {
            position: relative;
            flex-shrink: 0;
            max-width: 480px;
            margin: 0 auto;
        }

        .pwa-installer-banner.inline.show {
            display: flex;
        }

        .pwa-banner-left {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
            min-width: 0;
        }

        .pwa-banner-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            overflow: hidden;
            flex-shrink: 0;
            background: rgba(255, 255, 255, 0.1);
        }

        .pwa-banner-icon img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .pwa-banner-icon-fallback {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            font-size: 0.72rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pwa-banner-text {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .pwa-banner-title {
            font-size: 0.88rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .pwa-banner-sub {
            font-size: 0.72rem;
            opacity: 0.85;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .pwa-banner-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .pwa-banner-btn {
            background: rgba(255, 255, 255, 0.2);
            border: 1.5px solid rgba(255, 255, 255, 0.4);
            color: #fff;
            padding: 7px 16px;
            border-radius: 10px;
            font-size: 0.82rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
            transition: all 0.2s;
        }

        .pwa-banner-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .pwa-banner-dismiss {
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.6);
            font-size: 1.25rem;
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
        }

        .pwa-banner-dismiss:hover {
            color: #fff;
        }

        /* ── iOS Modal ── */
        .pwa-ios-modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            z-index: 99999;
            align-items: flex-end;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .pwa-ios-modal-overlay.open {
            display: flex;
            opacity: 1;
        }

        .pwa-ios-modal {
            background: #fff;
            border-radius: 28px 28px 0 0;
            width: 100%;
            max-width: 480px;
            padding: 0 20px 32px;
            transform: translateY(100%);
            transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            box-shadow: 0 -10px 40px rgba(0, 0, 0, 0.1);
        }

        .pwa-ios-modal-overlay.open .pwa-ios-modal {
            transform: translateY(0);
        }

        .pwa-ios-modal-handle {
            width: 40px;
            height: 4px;
            background: #e2e8f0;
            border-radius: 2px;
            margin: 12px auto 20px;
        }

        .pwa-ios-steps {
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin-bottom: 20px;
        }

        .pwa-ios-step {
            display: flex;
            gap: 14px;
            align-items: flex-start;
        }

        .pwa-ios-step-num {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #4f46e5;
            color: #fff;
            font-size: 0.85rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .pwa-ios-step-body {
            flex: 1;
        }

        .pwa-ios-step-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 3px;
        }

        .pwa-ios-step-sub {
            font-size: 0.8rem;
            color: #64748b;
            line-height: 1.4;
        }

        .pwa-ios-icon-badge {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            background: #f1f5f9;
            border-radius: 6px;
            font-size: 0.85rem;
            color: #334155;
        }

        .pwa-ios-close-btn {
            width: 100%;
            padding: 14px;
            background: #4f46e5;
            color: #fff;
            border: none;
            border-radius: 14px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            font-family: inherit;
            transition: background 0.2s;
        }

        .pwa-ios-close-btn:hover {
            background: #4338ca;
        }
    </style>
@endonce

{{-- Banner UI --}}
<div class="pwa-installer-banner {{ $floating ? 'floating' : 'inline' }}" id="pwaInstallBanner">
    <div class="pwa-banner-left">
        <div class="pwa-banner-icon">
            <img src="{{ asset('assets/img/icon-192.png') }}" alt="SSC" width="40" height="40"
                onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
            <div class="pwa-banner-icon-fallback" style="display:none;">SSC</div>
        </div>
        <div class="pwa-banner-text">
            <span class="pwa-banner-title">Install App</span>
            <span class="pwa-banner-sub">Add SSC Student to your home screen</span>
        </div>
    </div>
    <div class="pwa-banner-actions">
        <button class="pwa-banner-btn" id="pwaInstallBtn" onclick="handlePwaInstall()">
            <i class="bi bi-download"></i> Install
        </button>
        <button class="pwa-banner-dismiss" onclick="dismissPwaBanner()" title="Dismiss">
            <i class="bi bi-x"></i>
        </button>
    </div>
</div>

{{-- iOS Instructions Modal --}}
<div class="pwa-ios-modal-overlay" id="pwaIosModal" onclick="closePwaIosModal()">
    <div class="pwa-ios-modal" onclick="event.stopPropagation()">
        <div class="pwa-ios-modal-handle"></div>
        <div style="text-align:center;padding:0 4px 4px;">
            <div style="font-size:1.5rem;margin-bottom:8px;">📲</div>
            <div style="font-size:1.1rem;font-weight:800;color:#0f172a;margin-bottom:6px;">Add to Home Screen</div>
            <div style="font-size:0.82rem;color:#64748b;margin-bottom:20px;line-height:1.5;">Follow these steps to
                install the SSC Student app on your iPhone or iPad:</div>
        </div>
        <div class="pwa-ios-steps">
            <div class="pwa-ios-step">
                <div class="pwa-ios-step-num">1</div>
                <div class="pwa-ios-step-body">
                    <div class="pwa-ios-step-title">Tap the Share button</div>
                    <div class="pwa-ios-step-sub">Find <span class="pwa-ios-icon-badge"><i
                                class="bi bi-box-arrow-up"></i></span> in Safari's bottom toolbar</div>
                </div>
            </div>
            <div class="pwa-ios-step">
                <div class="pwa-ios-step-num">2</div>
                <div class="pwa-ios-step-body">
                    <div class="pwa-ios-step-title">Tap "Add to Home Screen"</div>
                    <div class="pwa-ios-step-sub">Scroll down in the share sheet and tap <strong>"Add to Home
                            Screen"</strong></div>
                </div>
            </div>
            <div class="pwa-ios-step">
                <div class="pwa-ios-step-num">3</div>
                <div class="pwa-ios-step-body">
                    <div class="pwa-ios-step-title">Tap "Add" to confirm</div>
                    <div class="pwa-ios-step-sub">The SSC Student app will appear on your home screen</div>
                </div>
            </div>
        </div>
        <div style="padding-top:8px;">
            <button onclick="closePwaIosModal()" class="pwa-ios-close-btn">Got it!</button>
        </div>
    </div>
</div>

@once
    <script>
        const isPwaIos = /iPhone|iPad|iPod/i.test(navigator.userAgent);
        const isPwaAndroid = /Android/i.test(navigator.userAgent);
        const isPwaMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        const isPwaStandalone = window.navigator.standalone === true || window.matchMedia('(display-mode: standalone)').matches;
        const PWA_DISMISSED_KEY = 'ssc_install_dismissed';
        const PWA_APK_URL = "{{ asset('downloads/ssc-student-app.apk') }}";

        let deferredPwaPrompt = null;

        // Listen for standard native install prompt
        window.addEventListener('beforeinstallprompt', e => {
            e.preventDefault();
            deferredPwaPrompt = e;
            showPwaInstallBanner();
        });

        // Initialize banner based on device
        window.addEventListener('DOMContentLoaded', () => {
            initPwaBanner();
        });

        function initPwaBanner() {
            const banner = document.getElementById('pwaInstallBanner');
            if (!banner) return;

            // Don't show if running inside the standalone app already
            if (isPwaStandalone) {
                banner.style.display = 'none';
                return;
            }

            // Don't show if user previously dismissed
            if (localStorage.getItem(PWA_DISMISSED_KEY) === '1') {
                banner.style.display = 'none';
                return;
            }

            // Only show automatically on mobile devices
            if (isPwaMobile) {
                showPwaInstallBanner();
            }
        }

        function showPwaInstallBanner() {
            const banner = document.getElementById('pwaInstallBanner');
            if (banner && localStorage.getItem(PWA_DISMISSED_KEY) !== '1' && !isPwaStandalone) {
                banner.classList.add('show');
                // Ensure layout takes it into account if inline
                if (!banner.classList.contains('floating')) {
                    banner.style.display = 'flex';
                }
            }
        }

        function handlePwaInstall() {
            if (deferredPwaPrompt) {
                // Native PWA Prompt (Android Chrome, Edge, etc.)
                deferredPwaPrompt.prompt();
                deferredPwaPrompt.userChoice.then(choice => {
                    if (choice.outcome === 'accepted') {
                        dismissPwaBanner();
                    }
                    deferredPwaPrompt = null;
                });
            } else if (isPwaIos) {
                // Safari iOS step-by-step
                openPwaIosModal();
            } else if (isPwaAndroid) {
                // Android but no PWA install event fired — fallback to APK download
                downloadPwaApk();
            } else {
                // Desktop fallback or others
                downloadPwaApk();
            }
        }

        function downloadPwaApk() {
            const a = document.createElement('a');
            a.href = PWA_APK_URL;
            a.download = 'ssc-student-app.apk';
            a.style.display = 'none';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }

        function dismissPwaBanner() {
            localStorage.setItem(PWA_DISMISSED_KEY, '1');
            const banner = document.getElementById('pwaInstallBanner');
            if (banner) {
                if (banner.classList.contains('floating')) {
                    banner.style.opacity = '0';
                    banner.style.transform = 'translateX(-50%) translateY(100px)';
                    setTimeout(() => banner.style.display = 'none', 400);
                } else {
                    banner.style.display = 'none';
                }
            }
        }

        function openPwaIosModal() {
            const modal = document.getElementById('pwaIosModal');
            if (modal) {
                modal.style.display = 'flex';
                requestAnimationFrame(() => modal.classList.add('open'));
            }
        }

        function closePwaIosModal() {
            const modal = document.getElementById('pwaIosModal');
            if (modal) {
                modal.classList.remove('open');
                setTimeout(() => modal.style.display = 'none', 300);
            }
        }

        // Hide banner when app is successfully installed
        window.addEventListener('appinstalled', dismissPwaBanner);

        // Register Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => {
                        console.log('PWA Service Worker registered with scope: ', reg.scope);
                    })
                    .catch(err => {
                        console.warn('PWA Service Worker registration failed: ', err);
                    });
            });
        }
    </script>
@endonce
