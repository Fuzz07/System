<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — SSC Transparency System</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/ssc_logo.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/icon-192.png') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="SSC Student">
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

        @if(session('success'))
        <div class="alert alert-success" style="border-radius:var(--radius-sm);font-size:.85rem;">
            {{ session('success') }}
        </div>
        @endif

        <div id="js-error-alert" class="alert alert-danger" style="display:none;border-radius:var(--radius-sm);font-size:.85rem;"></div>

        <form method="POST" action="{{ route('login.submit') }}" id="loginForm">
            @csrf
            <input type="hidden" name="portal" value="{{ $portal }}">
            <input type="hidden" name="latitude" id="login_latitude">
            <input type="hidden" name="longitude" id="login_longitude">
            <div style="display:none !important;" aria-hidden="true">
                <input type="text" name="website_url" tabindex="-1" autocomplete="off">
            </div>

            <div class="mb-3">
                <label class="form-label-custom">Email Address</label>
                <input type="email" name="email" class="form-control-custom" placeholder="user@mcclawis.edu.ph" value="{{ old('email') }}" required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label-custom">Password</label>
                <div style="position: relative;">
                    <input type="password" name="password" id="login_password" class="form-control-custom" placeholder="Enter your password" required style="padding-right: 44px;">
                    <button type="button" onclick="togglePasswordVisibility('login_password', this)" style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); background: none; border: none; padding: 0; color: #64748b; font-size: 1.15rem; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 10;">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            @if($portal === 'student')
            <div class="mb-4 text-end" style="margin-top:-16px;">
                <a href="{{ route('password.request') }}" style="color:var(--primary-light);font-weight:600;font-size:.82rem;text-decoration:none;">Forgot Password?</a>
            </div>
            @endif

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
  <div id="loading-title" style="font-size: 1.25rem; font-weight: 700; letter-spacing: -0.2px; margin-bottom: 6px;">Authenticating...</div>
  <div id="loading-sub" style="font-size: 0.88rem; color: #94a3b8;">Securing your session, please wait.</div>
</div>

<style>
  @keyframes fadeInLoader {
    from { opacity: 0; }
    to { opacity: 1; }
  }
</style>

<script src="{{ asset('assets/js/main.js') }}"></script>
<script>
  const loginForm = document.getElementById('loginForm');
  const loadingOverlay = document.getElementById('login-loading-overlay');
  const loadingTitle = document.getElementById('loading-title');
  const loadingSub = document.getElementById('loading-sub');
  const jsErrorAlert = document.getElementById('js-error-alert');

  loginForm.addEventListener('submit', function (e) {
    const portal = "{{ $portal }}";
    
    // Clear any previous error
    if (jsErrorAlert) {
      jsErrorAlert.style.display = 'none';
      jsErrorAlert.innerHTML = '';
    }

    if (portal !== 'student') {
      // Intercept submit
      e.preventDefault();

      // Check if geolocation is supported
      if (!navigator.geolocation) {
        showJsError('Your browser does not support location services. Location is required to log in to this portal.');
        return;
      }

      // Show overlay and set text for location acquisition
      loadingTitle.textContent = 'Acquiring Location...';
      loadingSub.textContent = 'Please allow location access in your browser.';
      loadingOverlay.style.display = 'flex';

      navigator.geolocation.getCurrentPosition(
        function (position) {
          // Set inputs
          document.getElementById('login_latitude').value = position.coords.latitude;
          document.getElementById('login_longitude').value = position.coords.longitude;

          // Update overlay to show authentication
          loadingTitle.textContent = 'Authenticating...';
          loadingSub.textContent = 'Securing your session, please wait.';

          // Submit the form
          loginForm.submit();
        },
        function (error) {
          loadingOverlay.style.display = 'none';
          
          let errMsg = 'Location permission is required to log in to this portal. Please enable and allow location services in your browser settings.';
          if (error.code === error.TIMEOUT) {
            errMsg = 'Location request timed out. Please try again.';
          } else if (error.code === error.POSITION_UNAVAILABLE) {
            errMsg = 'Your location information is currently unavailable. Please make sure location services are turned on.';
          }
          
          showJsError(errMsg);
        },
        {
          enableHighAccuracy: true,
          timeout: 10000,
          maximumAge: 0
        }
      );
    } else {
      // Student portal - submit normally
      loadingTitle.textContent = 'Authenticating...';
      loadingSub.textContent = 'Securing your session, please wait.';
      loadingOverlay.style.display = 'flex';
    }
  });

  function showJsError(msg) {
    if (jsErrorAlert) {
      jsErrorAlert.textContent = msg;
      jsErrorAlert.style.display = 'block';
      jsErrorAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
    } else {
      alert(msg);
    }
  }
</script>
@include('partials.pwa-installer', ['floating' => true])
</body>
</html>
