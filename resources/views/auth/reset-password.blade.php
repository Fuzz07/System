<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.security-guard')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password — SSC Transparency System</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/ssc_logo.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/icon-192.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
</head>
<body>
<div class="login-page">
    @include('partials.auth-backdrop')

    <div class="login-card" style="max-width: 520px;">
        <div class="login-logo" style="background: none; box-shadow: none; width: 100px; height: 100px;">
            <img src="{{ asset('assets/images/ssc_logo.png') }}" alt="SSC Logo" style="width: 100%; height: 100%; object-fit: contain;">
        </div>
        <h1 class="login-title">Reset Password</h1>
        <p class="login-sub">Enter the 6-digit code and choose a new password</p>

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

        <form method="POST" action="{{ route('password.update') }}" id="resetForm">
            @csrf
            
            <div class="mb-3">
                <label class="form-label-custom">Microsoft 365 Account (Outlook)</label>
                <input type="email" name="email" class="form-control-custom" placeholder="user@mcclawis.edu.ph" value="{{ old('email', session('reset_password_email')) }}" required style="background:#fff;">
            </div>

            <div class="mb-3">
                <label class="form-label-custom">6-Digit Verification Code (OTP)</label>
                <input type="text" name="otp" class="form-control-custom text-center fw-bold" placeholder="000000" pattern="\d{6}" maxlength="6" value="{{ old('otp') }}" required style="letter-spacing:4px; font-size:1.15rem; background:#fff;">
                <div class="form-text text-muted" style="font-size:0.75rem;margin-top:6px;">
                    Enter the code sent to your Outlook inbox.
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label-custom">New Password</label>
                <div style="position: relative;">
                    <input type="password" name="password" id="reset_password" class="form-control-custom" placeholder="Minimum 8 chars, with letters and numbers" required style="background:#fff; padding-right: 44px;">
                    <button type="button" onclick="togglePasswordVisibility('reset_password', this)" style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); background: none; border: none; padding: 0; color: #64748b; font-size: 1.15rem; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 10;">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label-custom">Confirm New Password</label>
                <div style="position: relative;">
                    <input type="password" name="password_confirmation" id="reset_password_confirmation" class="form-control-custom" placeholder="Confirm your new password" required style="background:#fff; padding-right: 44px;">
                    <button type="button" onclick="togglePasswordVisibility('reset_password_confirmation', this)" style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); background: none; border: none; padding: 0; color: #64748b; font-size: 1.15rem; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 10;">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-primary-custom w-100 justify-content-center" style="padding:14px;">
                <i class="bi bi-shield-lock-fill"></i> Save and Reset Password
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="{{ route('password.request') }}" class="text-muted" style="font-weight:600;font-size:.85rem;text-decoration:none;">
                <i class="bi bi-arrow-left"></i> Resend Code
            </a>
            <span class="text-muted mx-2">&bull;</span>
            <a href="{{ route('login') }}" class="text-muted" style="font-weight:600;font-size:.85rem;text-decoration:none;">
                Login Instead
            </a>
        </div>
    </div>
</div>

<!-- Authentic Fullscreen Loading Transition Overlay -->
<div id="reset-loading-overlay" style="display:none;position:fixed;inset:0;background:rgba(10,15,29,0.75);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);z-index:99999;align-items:center;justify-content:center;color:#fff;flex-direction:column;font-family:'Plus Jakarta Sans',sans-serif;animation:fadeInLoader 0.3s ease;">
  <div class="spinner-border text-primary" role="status" style="width: 3.5rem; height: 3.5rem; border-width: 0.35em; margin-bottom: 20px;"></div>
  <div style="font-weight: 700; font-size: 1.15rem; letter-spacing: 0.5px;">Saving New Password...</div>
  <div style="font-size: 0.88rem; color: #a1a1aa; margin-top: 6px;">Updating Cryptographic Hashes securely in Database</div>
</div>

<style>
  @keyframes fadeInLoader {
    from { opacity: 0; backdrop-filter: blur(0); }
    to { opacity: 1; backdrop-filter: blur(12px); }
  }
</style>

<script src="{{ asset('assets/js/main.js') }}"></script>
<script>
  document.getElementById('resetForm').addEventListener('submit', () => {
    document.getElementById('reset-loading-overlay').style.display = 'flex';
  });
</script>
</body>
</html>