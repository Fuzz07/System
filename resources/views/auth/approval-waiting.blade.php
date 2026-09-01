<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.security-guard')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Waiting for Authorization — SSC Transparency System</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/ssc_logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
</head>
<body>
<div class="login-page">
    @include('partials.auth-backdrop')

    <div class="login-card" style="max-width: 460px;">
        <div class="login-logo" style="background: none; box-shadow: none; width: 100px; height: 100px;">
            <img src="{{ asset('assets/images/ssc_logo.png') }}" alt="SSC Logo" style="width: 100%; height: 100%; object-fit: contain;">
        </div>
        <h1 class="login-title" style="font-size: 1.5rem;">Device Authorization</h1>
        <p class="login-sub">
            Your credentials are correct, but this is an unrecognized device. Please approve this login from your primary registered device.
        </p>

        <!-- Dynamic Visual Pulse Radar -->
        <div class="d-flex justify-content-center align-items-center my-4 py-2">
            <div class="position-relative d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                <div class="position-absolute rounded-circle bg-primary opacity-25 animate-ping" style="width: 100%; height: 100%; animation: pulseRadar 2s infinite ease-in-out;"></div>
                <div class="position-absolute rounded-circle bg-primary opacity-50 animate-ping" style="width: 70%; height: 70%; animation: pulseRadar 2s infinite ease-in-out; animation-delay: 0.6s;"></div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background-color: var(--primary); color: white; z-index: 2; box-shadow: 0 4px 12px rgba(10,15,29,0.35);">
                    <i class="bi bi-phone-vibrate" style="font-size: 1.75rem;"></i>
                </div>
            </div>
        </div>

        <div class="card mb-4" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
            <div class="card-body p-3" style="font-size: 0.8rem; text-align: left; color: #475569;">
                <div class="mb-1"><strong>Attempt Details:</strong></div>
                <div class="mb-1"><i class="bi bi-globe"></i> IP Address: <span style="font-family: monospace;">{{ $requestData['ip'] }}</span></div>
                <div class="mb-1"><i class="bi bi-laptop"></i> Platform: <span>{{ $requestData['user_agent'] }}</span></div>
                @if(!empty($requestData['latitude']) && !empty($requestData['longitude']))
                    <div class="d-flex align-items-center flex-wrap gap-1">
                        <i class="bi bi-geo-alt-fill text-danger"></i> Location Coords:
                        <a href="https://www.google.com/maps?q={{ urlencode($requestData['latitude'] . ',' . $requestData['longitude']) }}" target="_blank" rel="noopener noreferrer" class="badge bg-white text-danger border text-decoration-none shadow-sm d-inline-flex align-items-center gap-1 ms-1 px-2 py-1" style="font-size: 0.72rem; border-color: #fca5a5 !important; color: #b91c1c !important; background-color: #fef2f2 !important;" title="Open in Google Maps">
                            <span>{{ $requestData['latitude'] }}, {{ $requestData['longitude'] }}</span>
                            <i class="bi bi-box-arrow-up-right" style="font-size: 0.62rem;"></i>
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <div class="alert alert-info text-start py-2 px-3 mb-4" style="font-size: 0.78rem; border-radius: 6px;">
            <i class="bi bi-info-circle-fill"></i> Log in to your primary device and go to your **Dashboard**. A security prompt will automatically appear to approve this login.
        </div>

        <!-- Fallback Action Forms -->
        @php
            $host = request()->getHost();
            $isSubdomain = str_starts_with($host, 'admin.');
            $fallbackRoute = $isSubdomain ? route('admin.login.approval_fallback', $approvalId) : route('admin.login.approval_fallback.main', $approvalId);
            $loginRoute = route('login', ['portal' => 'admin']);
        @endphp

        <form method="POST" action="{{ $fallbackRoute }}" class="mb-3">
            @csrf
            <button type="submit" class="btn btn-primary w-100 py-2.5" style="font-weight: 600; font-size: 0.85rem; border-radius: var(--radius-sm);">
                <i class="bi bi-envelope-at"></i> Continue with Email Verification
            </button>
        </form>

        <div class="text-center">
            <a href="{{ $loginRoute }}" class="text-muted" style="font-size:.82rem;text-decoration:none;">
                <i class="bi bi-arrow-left"></i> Cancel and go back
            </a>
        </div>
    </div>
</div>

<style>
  @keyframes pulseRadar {
    0% { transform: scale(0.6); opacity: 0.8; }
    100% { transform: scale(1.6); opacity: 0; }
  }
</style>

<script>
  // Poller variables
  const approvalId = "{{ $approvalId }}";
  const isSubdomain = {{ $isSubdomain ? 'true' : 'false' }};
  const statusUrl = isSubdomain 
    ? `/login/approval-check/${approvalId}` 
    : `/login/admin/approval-check/${approvalId}`;
  
  const completeUrl = isSubdomain
    ? `/login/approval-complete/${approvalId}`
    : `/login/admin/approval-complete/${approvalId}`;

  const loginUrl = "{{ $loginRoute }}";

  // Polling function
  const intervalId = setInterval(async () => {
    try {
      const response = await fetch(statusUrl);
      if (!response.ok) return;
      const data = await response.json();

      if (data.status === 'approved') {
        clearInterval(intervalId);
        window.location.href = completeUrl;
      } else if (data.status === 'rejected') {
        clearInterval(intervalId);
        window.location.href = loginUrl + "?error=Login request was declined by primary device.";
      } else if (data.status === 'expired') {
        clearInterval(intervalId);
        window.location.href = loginUrl + "?error=Request timed out or expired.";
      }
    } catch (e) {
      console.error("Failed status check", e);
    }
  }, 2000);
</script>
</body>
</html>
