<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — SSC Transparency System</title>
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
    <div class="login-card" style="max-width:560px;">
        <div class="login-logo" style="background: none; box-shadow: none; width: 100px; height: 100px;">
            <img src="{{ asset('assets/images/ssc_logo.png') }}" alt="SSC Logo" style="width: 100%; height: 100%; object-fit: contain;">
        </div>
        <h1 class="login-title">Student Registration</h1>
        <p class="login-sub">Create your SSC transparency portal account</p>

        @if($errors->any())
        <div class="alert alert-danger" style="border-radius:var(--radius-sm);font-size:.85rem;">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <form method="POST" action="{{ route('register.submit') }}">
            @csrf
            <div style="display:none !important;" aria-hidden="true">
                <input type="text" name="website_url" tabindex="-1" autocomplete="off">
            </div>
            <div class="row g-2 mb-3">
                <div class="col-md-4"><label class="form-label-custom">First Name</label><input type="text" name="first_name" class="form-control-custom" value="{{ old('first_name') }}" required></div>
                <div class="col-md-4"><label class="form-label-custom">Middle Name</label><input type="text" name="middle_name" class="form-control-custom" value="{{ old('middle_name') }}"></div>
                <div class="col-md-4"><label class="form-label-custom">Last Name</label><input type="text" name="last_name" class="form-control-custom" value="{{ old('last_name') }}" required></div>
            </div>
            <div class="row g-2 mb-3">
                <div class="col-md-4"><label class="form-label-custom">Age</label><input type="number" name="age" class="form-control-custom" min="10" value="{{ old('age') }}" required></div>
                <div class="col-md-4"><label class="form-label-custom">Year Level</label>
                    <select name="year_level" class="form-select-custom" required>
                        <option value="">Select</option>
                        @foreach(['1st Year','2nd Year','3rd Year','4th Year'] as $y)
                        <option value="{{ $y }}" {{ old('year_level') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4"><label class="form-label-custom">Department</label><input type="text" name="department" class="form-control-custom" placeholder="e.g. BSIT" value="{{ old('department') }}" required></div>
            </div>
            <div class="row g-2 mb-3">
                <div class="col-md-6"><label class="form-label-custom">Student ID</label><input type="text" name="student_id" class="form-control-custom" pattern="\d{4}-\d{4}" title="Format: YYYY-XXXX" placeholder="YYYY-XXXX" value="{{ old('student_id') }}" required></div>
                <div class="col-md-6"><label class="form-label-custom">MS Account</label><input type="email" name="email" class="form-control-custom" placeholder="user@mcclawis.edu.ph" value="{{ old('email') }}" required></div>
            </div>
            <div class="row g-2 mb-4">
                <div class="col-md-6"><label class="form-label-custom">Password</label><input type="password" name="password" class="form-control-custom" minlength="6" required></div>
                <div class="col-md-6"><label class="form-label-custom">Confirm Password</label><input type="password" name="password_confirmation" class="form-control-custom" required></div>
            </div>

            @include('partials.captcha')

            <button type="submit" class="btn-primary-custom w-100 justify-content-center" style="padding:14px;">
                <i class="bi bi-person-plus"></i> Create Account
            </button>
        </form>

        <div class="text-center mt-3">
            <a href="{{ route('login', 'student') }}" class="text-muted" style="font-size:.82rem;text-decoration:none;">
                <i class="bi bi-arrow-left"></i> Already have an account? Login
            </a>
        </div>
    </div>
</div>
<script src="{{ asset('assets/js/main.js') }}"></script>
@include('partials.pwa-installer', ['floating' => true])
</body>
</html>
