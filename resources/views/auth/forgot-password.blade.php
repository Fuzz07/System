<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password — SSC Transparency System</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/ssc_logo.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/icon-192.png') }}">
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
        <h1 class="login-title">Forgot Password</h1>
        <p class="login-sub">Reset your SSC Student Portal Password</p>

        @if($errors->any())
        <div class="alert alert-danger" style="border-radius:var(--radius-sm);font-size:.85rem;">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
        @endif

        @if(session('success'))
        <div class="alert alert-success" style="border-radius:var(--radius-sm);font-size:.85rem;">
            {{ session('success') }}
        </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" id="forgotForm">
            @csrf
            
            <div class="mb-4">
                <label class="form-label-custom">Microsoft 365 Account (Outlook)</label>
                <input type="email" name="email" class="form-control-custom" placeholder="user@mcclawis.edu.ph" value="{{ old('email') }}" required autofocus style="background:#fff;">
                <div class="form-text text-muted" style="font-size:0.75rem;margin-top:6px;">
                    We will send a 6-digit password reset verification code to your official school Outlook inbox.
                </div>
            </div>

            <button type="submit" class="btn-primary-custom w-100 justify-content-center" style="padding:14px;">
                <i class="bi bi-send-fill"></i> Send Reset Code
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="{{ route('login') }}" class="text-muted" style="font-weight:600;font-size:.85rem;text-decoration:none;">
                <i class="bi bi-arrow-left"></i> Back to Login
            </a>
        </div>
    </div>
</div>

<!-- Authentic Fullscreen Loading Transition Overlay -->
<div id="forgot-loading-overlay" style="display:none;position:fixed;inset:0;background:rgba(10,15,29,0.75);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);z-index:99999;align-items:center;justify-content:center;color:#fff;flex-direction:column;font-family:'Plus Jakarta Sans',sans-serif;animation:fadeInLoader 0.3s ease;">
  <div class="spinner-border text-primary" role="status" style="width: 3.5rem; height: 3.5rem; border-width: 0.35em; margin-bottom: 20px;"></div>
  <div style="font-weight: 700; font-size: 1.15rem; letter-spacing: 0.5px;">Sending Verification Code...</div>
  <div style="font-size: 0.88rem; color: #a1a1aa; margin-top: 6px;">Communicating with Microsoft 365 Outlook Servers</div>
</div>

<style>
  @keyframes fadeInLoader {
    from { opacity: 0; backdrop-filter: blur(0); }
    to { opacity: 1; backdrop-filter: blur(12px); }
  }
</style>

<script>
  document.getElementById('forgotForm').addEventListener('submit', () => {
    document.getElementById('forgot-loading-overlay').style.display = 'flex';
  });
</script>
</body>
</html>