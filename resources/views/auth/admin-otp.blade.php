<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.security-guard')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Device Verification — SSC Transparency System</title>
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
        <h1 class="login-title">Admin OTP Verification</h1>
        <p class="login-sub">
            A secure verification code has been sent to your email address. Please enter it below to authorize this device and log in.
        </p>

        <div class="text-center mb-3 p-2" style="background: rgba(225,29,72,0.06); border: 1px dashed rgba(225,29,72,0.3); border-radius: 8px; font-size: 0.88rem; font-weight: 600; color: #e11d48;">
            <i class="bi bi-clock-history me-1"></i> Code expires in: <span id="otp-countdown" style="font-family: monospace; font-size: 1rem; font-weight: 700;">03:00</span>
        </div>

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

        <form method="POST" action="{{ url()->current() }}" id="otpForm">
            @csrf
            <div class="mb-4">
                <label class="form-label-custom">Verification Code (6 Digits)</label>
                <input type="text" name="otp" id="otpInput" class="form-control-custom text-center" placeholder="123456" maxlength="6" pattern="\d{6}" required autofocus style="font-size:1.5rem; letter-spacing: 0.25em; font-weight: bold; height: 56px;">
            </div>

            <button type="submit" id="verifyBtn" class="btn-primary-custom w-100 justify-content-center" style="padding:14px;">
                <i class="bi bi-shield-check"></i> Verify & Authenticate
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="{{ route('login', ['portal' => 'admin']) }}" class="text-muted" style="font-size:.82rem;text-decoration:none;">
                <i class="bi bi-arrow-left"></i> Back to Login
            </a>
        </div>
    </div>
</div>

<!-- Fullscreen Loading Transition Overlay -->
<div id="login-loading-overlay" style="display:none;position:fixed;inset:0;background:rgba(10,15,29,0.75);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);z-index:99999;align-items:center;justify-content:center;color:#fff;flex-direction:column;font-family:'Plus Jakarta Sans',sans-serif;">
  <div class="spinner-border text-primary" role="status" style="width: 3.5rem; height: 3.5rem; border-width: 0.35em; margin-bottom: 20px;"></div>
  <div style="font-size: 1.25rem; font-weight: 700; letter-spacing: -0.2px; margin-bottom: 6px;">Verifying Code...</div>
  <div style="font-size: 0.88rem; color: #94a3b8;">Securing your admin session, please wait.</div>
</div>

<script>
  const otpForm = document.getElementById('otpForm');
  const loadingOverlay = document.getElementById('login-loading-overlay');
  const countdownEl = document.getElementById('otp-countdown');
  const verifyBtn = document.getElementById('verifyBtn');
  const otpInput = document.getElementById('otpInput');

  otpForm.addEventListener('submit', function () {
    loadingOverlay.style.display = 'flex';
  });

  const expiresTimestamp = {{ $expiresTimestamp ?? (time() + 180) }};

  function updateOtpCountdown() {
    const now = Math.floor(Date.now() / 1000);
    const secondsRemaining = expiresTimestamp - now;

    if (secondsRemaining <= 0) {
      countdownEl.textContent = 'EXPIRED';
      countdownEl.style.color = '#dc2626';
      if (verifyBtn) {
        verifyBtn.disabled = true;
        verifyBtn.style.opacity = '0.6';
        verifyBtn.style.cursor = 'not-allowed';
        verifyBtn.innerHTML = '<i class="bi bi-clock-history me-1"></i> Code Expired — Please Request New Code';
      }
      if (otpInput) {
        otpInput.disabled = true;
      }
      return;
    }

    const mins = Math.floor(secondsRemaining / 60);
    const secs = secondsRemaining % 60;
    countdownEl.textContent = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
  }

  updateOtpCountdown();
  setInterval(updateOtpCountdown, 1000);
</script>
</body>
</html>
