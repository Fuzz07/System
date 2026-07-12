<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — SSC Transparency System</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/ssc_logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
</head>
<body>
<div class="login-page">
    <div class="login-card">
        <div class="login-logo" style="background: none; box-shadow: none; width: 100px; height: 100px;">
            <img src="{{ asset('assets/images/ssc_logo.png') }}" alt="SSC Logo" style="width: 100%; height: 100%; object-fit: contain;">
        </div>
        <h1 class="login-title">{{ ucfirst($portal) }} Login</h1>
        <p class="login-sub">
            @if($portal === 'admin') System Administration Portal
            @elseif($portal === 'treasurer') Treasurer Control Panel
            @elseif($portal === 'officer') Officer Management Portal
            @elseif($portal === 'dean') Dean Selection & Endorsement Portal
            @else Student Transparency Portal
            @endif
        </p>

        @if($errors->any())
        <div class="alert alert-danger" style="border-radius:var(--radius-sm);font-size:.85rem;">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('login.submit') }}" id="loginForm">
            @csrf
            <input type="hidden" name="portal" value="{{ $portal }}">
            <div style="display:none !important;" aria-hidden="true">
                <input type="text" name="website_url" tabindex="-1" autocomplete="off">
            </div>

            <div class="mb-3">
                <label class="form-label-custom">Email Address</label>
                <input type="email" name="email" class="form-control-custom" placeholder="user@mcclawis.edu.ph" value="{{ old('email') }}" required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label-custom">Password</label>
                <input type="password" name="password" class="form-control-custom" placeholder="Enter your password" required>
            </div>

            @include('partials.captcha')

            <button type="submit" class="btn-primary-custom w-100 justify-content-center" style="padding:14px;">
                <i class="bi bi-box-arrow-in-right"></i> Sign In
            </button>
        </form>

        @if($portal === 'student')
        <div class="text-center mt-4">
            <span class="text-muted" style="font-size:.85rem;">Don't have an account?</span>
            <a href="{{ route('register') }}" style="color:var(--primary-light);font-weight:600;font-size:.85rem;text-decoration:none;"> Register here</a>
        </div>
        @endif

        <div class="text-center mt-3">
            <a href="{{ route('home') }}" class="text-muted" style="font-size:.82rem;text-decoration:none;">
                <i class="bi bi-arrow-left"></i> Back to Portal Selection
            </a>
        </div>
    </div>
</div>

<!-- Authentic Fullscreen Loading Transition Overlay -->
<div id="login-loading-overlay" style="display:none;position:fixed;inset:0;background:rgba(10,15,29,0.75);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);z-index:99999;align-items:center;justify-content:center;color:#fff;flex-direction:column;font-family:'Plus Jakarta Sans',sans-serif;animation:fadeInLoader 0.3s ease;">
  <div class="spinner-border text-primary" role="status" style="width: 3.5rem; height: 3.5rem; border-width: 0.35em; margin-bottom: 20px;"></div>
  <div style="font-size: 1.25rem; font-weight: 700; letter-spacing: -0.2px; margin-bottom: 6px;">Authenticating...</div>
  <div style="font-size: 0.88rem; color: #94a3b8;">Securing your session, please wait.</div>
</div>

<style>
  @keyframes fadeInLoader {
    from { opacity: 0; }
    to { opacity: 1; }
  }
</style>

<script>
  document.getElementById('loginForm').addEventListener('submit', () => {
    document.getElementById('login-loading-overlay').style.display = 'flex';
  });
</script>
</body>
</html>
