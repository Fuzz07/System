<!DOCTYPE html>
<html lang="en" class="{{ Auth::check() ? 'role-' . Auth::user()->role : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle ?? 'SSC Transparency System' }} — SSC System</title>
    <meta name="description" content="SSC Transparency and Budget Allocation System — Supreme Student Council">
    <link rel="icon" type="image/png" href="{{ asset('assets/images/ssc_logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
</head>
<body>
    {{-- Sidebar --}}
    <aside class="sidebar" id="sidebar">
        <a href="{{ $dashUrl ?? '#' }}" class="sidebar-brand">
            <div class="brand-logo" style="background: none; box-shadow: none;">
                <img src="{{ asset('assets/images/ssc_logo.png') }}" alt="SSC Logo" style="width: 100%; height: 100%; object-fit: contain;">
            </div>
            <div class="brand-name">SSC System</div>
            <div class="brand-sub">Transparency & Budget</div>
        </a>

        <div class="sidebar-user">
            <div class="avatar">{{ Auth::user()->avatar }}</div>
            <div class="user-info">
                <div class="name">{{ Auth::user()->fullname }}</div>
                <div class="role-tag">{{ ucfirst(Auth::user()->role) }}</div>
            </div>
        </div>

        <nav class="sidebar-nav">
            @yield('sidebar-nav')
        </nav>

        <div class="sidebar-footer">
            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="logout-btn" style="background:none;border:none;width:100%;text-align:left;cursor:pointer;">
                    <i class="bi bi-box-arrow-left"></i> Sign Out
                </button>
            </form>
        </div>
    </aside>

    {{-- Sidebar Overlay --}}
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    {{-- Main Content --}}
    <div class="main-wrapper">
        <header class="topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn d-md-none p-0 border-0" id="sidebarToggle" style="font-size:1.25rem;color:var(--slate-600);">
                    <i class="bi bi-list"></i>
                </button>
                <div>
                    <div class="topbar-title">{{ $pageTitle ?? '' }}</div>
                    <div class="topbar-sub">{{ $pageSubtitle ?? '' }}</div>
                </div>
            </div>
            <div class="topbar-right">
                @yield('topbar-right')
            </div>
        </header>

        <main class="page-content">
            {{-- Flash Messages --}}
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius:var(--radius-sm);border:none;font-weight:600;">
                <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            @if(session('danger'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius:var(--radius-sm);border:none;font-weight:600;">
                <i class="bi bi-exclamation-circle-fill"></i> {{ session('danger') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert" style="border-radius:var(--radius-sm);border:none;font-weight:600;">
                <i class="bi bi-exclamation-triangle-fill"></i> {{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius:var(--radius-sm);border:none;">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @yield('content')
        </main>
    </div>
    @if(session('show_app_download'))
    <!-- App Download Invitation Modal -->
    <div class="modal fade" id="appDownloadModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="appDownloadModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 24px; overflow: hidden;">
                <div class="row g-0">
                    <!-- Left side: Banner & Features -->
                    <div class="col-md-6 text-white p-5 d-flex flex-column justify-content-between" style="background: linear-gradient(135deg, #e34f26 0%, #d13f19 100%);">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-4">
                                <img src="{{ asset('assets/images/ssc_logo.png') }}" alt="Logo" width="40" height="40" style="filter: drop-shadow(0 4px 6px rgba(0,0,0,0.15));">
                                <span class="fw-bold text-uppercase tracking-wider" style="font-size: 0.9rem; letter-spacing: 1px;">SSC Portal</span>
                            </div>
                            <h3 class="fw-extrabold mb-3" style="font-weight: 800; font-size: 1.8rem; line-height: 1.2;">Get the Mobile App</h3>
                            <p class="text-white-50 mb-4" style="font-size: 0.9rem; line-height: 1.5;">Take the SSC Transparency System with you. Stay connected, discuss active proposals, and cast your vote on the go.</p>
                            
                            <div class="d-flex flex-column gap-3 mb-4">
                                <div class="d-flex align-items-start gap-3">
                                    <span class="bg-white bg-opacity-20 rounded-circle p-1 d-inline-flex justify-content-center align-items-center" style="width: 28px; height: 28px; flex-shrink: 0;">
                                        <i class="bi bi-bell-fill" style="font-size: 0.85rem;"></i>
                                    </span>
                                    <div>
                                        <h5 class="mb-0 fw-bold" style="font-size: 0.95rem;">Push Notifications</h5>
                                        <p class="text-white-50 mb-0 small">Get notified immediately when new proposals are posted.</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-start gap-3">
                                    <span class="bg-white bg-opacity-20 rounded-circle p-1 d-inline-flex justify-content-center align-items-center" style="width: 28px; height: 28px; flex-shrink: 0;">
                                        <i class="bi bi-lightning-charge-fill" style="font-size: 0.85rem;"></i>
                                    </span>
                                    <div>
                                        <h5 class="mb-0 fw-bold" style="font-size: 0.95rem;">Faster Access</h5>
                                        <p class="text-white-50 mb-0 small">One-tap access without logging in repeatedly.</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-start gap-3">
                                    <span class="bg-white bg-opacity-20 rounded-circle p-1 d-inline-flex justify-content-center align-items-center" style="width: 28px; height: 28px; flex-shrink: 0;">
                                        <i class="bi bi-patch-check-fill" style="font-size: 0.85rem;"></i>
                                    </span>
                                    <div>
                                        <h5 class="mb-0 fw-bold" style="font-size: 0.95rem;">Secure Student Voting</h5>
                                        <p class="text-white-50 mb-0 small">Secure and native experience for school year voting.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-white-50 small">Developed in Kotlin for Android devices.</div>
                    </div>
                    
                    <!-- Right side: QR Code and Direct Download Button -->
                    <div class="col-md-6 p-5 d-flex flex-column justify-content-between bg-white position-relative">
                        <button type="button" class="btn-close position-absolute top-0 end-0 m-4" data-bs-dismiss="modal" aria-label="Close"></button>
                        
                        <div class="text-center my-auto">
                            <div class="mb-3">
                                <span class="badge px-3 py-2 fw-semibold rounded-pill" style="background-color: rgba(227, 79, 38, 0.1); color: #e34f26;">Available Now</span>
                            </div>
                            <h4 class="fw-bold text-dark mb-3" style="font-size: 1.3rem;">Scan to Download APK</h4>
                            
                            <!-- QR Code Container -->
                            <div class="d-inline-block p-3 border rounded-4 mb-4 bg-light shadow-sm" style="border-color: #e2e8f0 !important;">
                                <img id="appDownloadQr" src="" alt="Loading QR Code..." width="150" height="150" style="display: block;">
                            </div>
                            
                            <div class="mb-3">
                                <a href="{{ asset('downloads/ssc-student-app.apk') }}" download class="btn w-100 py-3 fw-bold text-white shadow" style="background: linear-gradient(135deg, #e34f26 0%, #d13f19 100%); border: none; border-radius: 14px; font-size: 1rem; transition: transform 0.2s, box-shadow 0.2s;">
                                    <i class="bi bi-android2 me-2"></i> Download APK Direct
                                </a>
                            </div>
                            
                            <p class="text-muted mb-0 small">
                                Can't scan? Tap the download button directly or visit this page on your mobile device.
                            </p>
                        </div>
                        
                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-link text-decoration-none text-muted small fw-semibold" data-bs-dismiss="modal">
                                Maybe Later, go to dashboard
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var myModal = new bootstrap.Modal(document.getElementById('appDownloadModal'), {
                keyboard: false
            });
            
            // Generate dynamic QR code URL
            var downloadUrl = window.location.origin + '/downloads/ssc-student-app.apk';
            var qrImg = document.getElementById('appDownloadQr');
            if (qrImg) {
                qrImg.src = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' + encodeURIComponent(downloadUrl);
            }
            
            myModal.show();
        });
    </script>
    @endif

    @yield('chatbot')
    @include('partials.logout-modal')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
    @stack('scripts')
</body>
</html>
