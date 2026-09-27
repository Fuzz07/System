{{-- ══════════════════════════════════════
APP INSTALL GUIDE — "!" button that opens step-by-step instructions for installing
the SSC Student Android app (APK). Place it next to any "download app" button.
Works without Bootstrap JS (the landing page doesn't load it).
Usage (tone "dark" for light backgrounds is the default; use "light" on dark backgrounds):
  @include('partials.app-install-guide')
  @include('partials.app-install-guide', ['tone' => 'light'])
═══════════════════════════════════════ --}}

@php
    $tone = ($tone ?? 'dark') === 'light' ? 'light' : 'dark';
@endphp

<button type="button" class="app-guide-trigger app-guide-trigger--{{ $tone }}" onclick="openAppInstallGuide(this)"
    title="How to install the app" aria-label="How to install the mobile app" aria-haspopup="dialog">!</button>

@once
    <style>
        .app-guide-trigger {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 24px;
            height: 24px;
            padding: 0;
            border-radius: 50%;
            font: 800 0.85rem/1 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            cursor: pointer;
            vertical-align: middle;
            transition: transform 0.15s ease, background 0.15s ease;
        }

        .app-guide-trigger:hover { transform: scale(1.1); }
        .app-guide-trigger:focus-visible { outline: 2px solid #e34f26; outline-offset: 2px; }

        .app-guide-trigger--dark {
            background: rgba(227, 79, 38, 0.1);
            border: 1.5px solid #e34f26;
            color: #e34f26;
        }

        .app-guide-trigger--light {
            background: rgba(255, 255, 255, 0.12);
            border: 1.5px solid rgba(255, 255, 255, 0.7);
            color: #fff;
        }

        .app-guide-overlay {
            position: fixed;
            inset: 0;
            z-index: 2000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            background: rgba(15, 23, 42, 0.55);
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        }

        .app-guide-overlay[hidden] { display: none; }
        .app-guide-overlay * { box-sizing: border-box; }

        .app-guide-dialog {
            width: 100%;
            max-width: 460px;
            max-height: calc(100vh - 32px);
            overflow-y: auto;
            background: #fff;
            color: #0f172a;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.3);
            padding: 24px 22px 20px;
            text-align: left;
        }

        .app-guide-head { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 18px; }

        .app-guide-head-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: rgba(227, 79, 38, 0.1);
            color: #e34f26;
            font-size: 1.3rem;
        }

        .app-guide-title { margin: 0 0 4px; font-size: 1.1rem; font-weight: 800; line-height: 1.3; }
        .app-guide-sub { margin: 0; font-size: 0.8rem; color: #64748b; line-height: 1.5; }

        .app-guide-close {
            margin-left: auto;
            flex-shrink: 0;
            width: 32px;
            height: 32px;
            border: none;
            border-radius: 50%;
            background: #f1f5f9;
            color: #475569;
            font-size: 1.1rem;
            line-height: 1;
            cursor: pointer;
        }

        .app-guide-steps { list-style: none; margin: 0 0 16px; padding: 0; display: flex; flex-direction: column; gap: 14px; }
        .app-guide-step { display: flex; gap: 12px; align-items: flex-start; }

        .app-guide-num {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: #e34f26;
            color: #fff;
            font-size: 0.8rem;
            font-weight: 800;
        }

        .app-guide-step-title { font-size: 0.9rem; font-weight: 700; margin-bottom: 2px; }
        .app-guide-step-text { font-size: 0.8rem; color: #475569; line-height: 1.5; }

        .app-guide-note {
            margin: 0 0 10px;
            padding: 10px 12px;
            border-radius: 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            font-size: 0.76rem;
            color: #475569;
            line-height: 1.5;
        }

        .app-guide-actions { display: flex; gap: 10px; margin-top: 16px; }

        .app-guide-download,
        .app-guide-done {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px;
            border-radius: 12px;
            font-size: 0.88rem;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }

        .app-guide-download { background: #e34f26; border: none; color: #fff; }
        .app-guide-download:hover { background: #d13f19; color: #fff; }
        .app-guide-done { background: #fff; border: 1px solid #cbd5e1; color: #334155; }
    </style>

    <template id="appInstallGuideTemplate">
        <div class="app-guide-overlay" hidden role="dialog" aria-modal="true" aria-labelledby="appGuideTitle">
            <div class="app-guide-dialog">
                <div class="app-guide-head">
                    <span class="app-guide-head-icon"><i class="bi bi-android2"></i></span>
                    <div>
                        <h2 class="app-guide-title" id="appGuideTitle">How to install the SSC Student app</h2>
                        <p class="app-guide-sub">For Android phones (Android 7.0 or newer). It's free and takes about a minute.</p>
                    </div>
                    <button type="button" class="app-guide-close" data-app-guide-close aria-label="Close">&times;</button>
                </div>

                <ol class="app-guide-steps">
                    <li class="app-guide-step">
                        <span class="app-guide-num">1</span>
                        <div>
                            <div class="app-guide-step-title">Download the app</div>
                            <div class="app-guide-step-text">Tap <strong>Download APK</strong> below. If your browser warns that the file might be harmful, tap <strong>Download anyway</strong> &mdash; the file comes straight from the SSC portal.</div>
                        </div>
                    </li>
                    <li class="app-guide-step">
                        <span class="app-guide-num">2</span>
                        <div>
                            <div class="app-guide-step-title">Open the downloaded file</div>
                            <div class="app-guide-step-text">Tap the download notification, or open your <strong>Files</strong> app &rarr; <strong>Downloads</strong> &rarr; <strong>ssc-student-app.apk</strong>.</div>
                        </div>
                    </li>
                    <li class="app-guide-step">
                        <span class="app-guide-num">3</span>
                        <div>
                            <div class="app-guide-step-title">Allow the installation</div>
                            <div class="app-guide-step-text">If your phone says it isn't allowed to install unknown apps, tap <strong>Settings</strong>, turn on <strong>Allow from this source</strong>, then go back.</div>
                        </div>
                    </li>
                    <li class="app-guide-step">
                        <span class="app-guide-num">4</span>
                        <div>
                            <div class="app-guide-step-title">Tap Install, then Open</div>
                            <div class="app-guide-step-text">If Google Play Protect shows a warning, tap <strong>More details</strong> &rarr; <strong>Install anyway</strong>.</div>
                        </div>
                    </li>
                    <li class="app-guide-step">
                        <span class="app-guide-num">5</span>
                        <div>
                            <div class="app-guide-step-title">Log in</div>
                            <div class="app-guide-step-text">Sign in with your student account, the same one you use on the website.</div>
                        </div>
                    </li>
                </ol>

                <p class="app-guide-note"><strong>Updating?</strong> Download the latest version and install it over the old one. You don't need to uninstall first.</p>
                <p class="app-guide-note"><strong>Using an iPhone?</strong> The app is Android only. Open the portal in Safari, tap <strong>Share</strong>, then <strong>Add to Home Screen</strong>.</p>

                <div class="app-guide-actions">
                    <a class="app-guide-download" href="{{ asset('downloads/ssc-student-app.apk') }}?v=1.8" download>
                        <i class="bi bi-download"></i> Download APK
                    </a>
                    <button type="button" class="app-guide-done" data-app-guide-close>Got it</button>
                </div>
            </div>
        </div>
    </template>

    <script>
        (function () {
            let overlay = null;
            let returnFocus = null;

            function close() {
                if (!overlay || overlay.hidden) return;
                overlay.hidden = true;
                document.removeEventListener('keydown', onKey);
                if (returnFocus) returnFocus.focus();
            }

            function onKey(event) {
                if (event.key === 'Escape') close();
            }

            window.openAppInstallGuide = function (trigger) {
                if (!overlay) {
                    overlay = document.getElementById('appInstallGuideTemplate').content.firstElementChild.cloneNode(true);
                    overlay.addEventListener('click', function (event) {
                        if (event.target === overlay || event.target.closest('[data-app-guide-close]')) close();
                    });
                }
                // Inside a Bootstrap modal, mount there so its focus trap doesn't steal focus from the guide.
                const host = (trigger && trigger.closest('.modal')) || document.body;
                if (overlay.parentNode !== host) host.appendChild(overlay);

                returnFocus = trigger || null;
                overlay.hidden = false;
                document.addEventListener('keydown', onKey);
                overlay.querySelector('.app-guide-close').focus();
            };
        })();
    </script>
@endonce
