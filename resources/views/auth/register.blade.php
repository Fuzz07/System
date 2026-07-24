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

        <form method="POST" action="{{ route('register.submit') }}" id="registerForm">
            @csrf
            <div style="display:none !important;" aria-hidden="true">
                <input type="text" name="website_url" tabindex="-1" autocomplete="off">
            </div>

            <!-- STEP 1: Basic Information & MS Account Verification -->
            <div id="step-1">
                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <label class="form-label-custom">First Name</label>
                        <input type="text" name="first_name" class="form-control-custom" value="{{ old('first_name') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-custom">Middle Name</label>
                        <input type="text" name="middle_name" class="form-control-custom" value="{{ old('middle_name') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-custom">Last Name</label>
                        <input type="text" name="last_name" class="form-control-custom" value="{{ old('last_name') }}" required>
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <label class="form-label-custom">Age</label>
                        <input type="number" name="age" class="form-control-custom" min="10" value="{{ old('age') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-custom">Year Level</label>
                        <select name="year_level" class="form-select-custom" required>
                            <option value="">Select</option>
                            @foreach(['1st Year','2nd Year','3rd Year','4th Year'] as $y)
                            <option value="{{ $y }}" {{ old('year_level') == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-custom">Department</label>
                        <input type="text" name="department" class="form-control-custom" placeholder="e.g. BSIT" value="{{ old('department') }}" required>
                    </div>
                </div>
                <div class="row g-2 mb-4">
                    <div class="col-md-6">
                        <label class="form-label-custom">Student ID</label>
                        <input type="text" name="student_id" class="form-control-custom" pattern="\d{4}-\d{4}" title="Format: YYYY-XXXX" placeholder="YYYY-XXXX" value="{{ old('student_id') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-custom">MS Account</label>
                        <input type="email" name="email" class="form-control-custom" placeholder="user@mcclawis.edu.ph" value="{{ old('email') }}" required>
                    </div>
                </div>

                <div id="step-1-error" class="alert alert-danger d-none mb-3" style="border-radius:var(--radius-sm);font-size:.85rem;"></div>

                <button type="button" id="btn-next-step" class="btn-primary-custom w-100 justify-content-center" style="padding:14px;">
                    <span id="next-btn-text"><i class="bi bi-arrow-right-circle"></i> Verify &amp; Continue</span>
                    <div id="next-btn-spinner" class="spinner-border spinner-border-sm text-white d-none" role="status" style="margin-left:8px;"></div>
                </button>
            </div>

            <!-- STEP 2: Password Creation & CAPTCHA Verification -->
            <div id="step-2" class="d-none">
                <div class="alert alert-success mb-3" style="border-radius:var(--radius-sm);font-size:.85rem;">
                    <i class="bi bi-check-circle-fill"></i> MS Account verified successfully! Please secure your account by creating a password.
                </div>

                <div class="row g-2 mb-4">
                    <div class="col-md-6">
                        <label class="form-label-custom">Password</label>
                        <input type="password" name="password" id="register_password" class="form-control-custom" minlength="8" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-custom">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="register_password_confirmation" class="form-control-custom" minlength="8" required>
                    </div>
                </div>

                @include('partials.captcha')

                <div class="d-flex gap-2">
                    <button type="button" id="btn-back-step" class="btn-secondary-custom" style="padding:14px; width:110px; display:flex; align-items:center; justify-content:center; gap:6px; font-weight:600; border-radius:var(--radius-sm); border:1.5px solid #cbd5e1; background:#fff; color:#475569;">
                        <i class="bi bi-arrow-left-circle"></i> Back
                    </button>
                    <button type="submit" class="btn-primary-custom flex-grow-1 justify-content-center" style="padding:14px;">
                        <i class="bi bi-person-plus"></i> Complete Registration
                    </button>
                </div>
            </div>
        </form>

        <div class="text-center mt-3">
            <a href="{{ route('login', 'student') }}" class="text-muted" style="font-size:.82rem;text-decoration:none;">
                <i class="bi bi-arrow-left"></i> Already have an account? Login
            </a>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const btnNextStep = document.getElementById('btn-next-step');
        const btnBackStep = document.getElementById('btn-back-step');
        const step1 = document.getElementById('step-1');
        const step2 = document.getElementById('step-2');
        const step1Error = document.getElementById('step-1-error');
        const nextBtnText = document.getElementById('next-btn-text');
        const nextBtnSpinner = document.getElementById('next-btn-spinner');
        const registerForm = document.getElementById('registerForm');

        if (!btnNextStep) return;

        // STEP 1 TO STEP 2 transition (with real-time MS account check)
        btnNextStep.addEventListener('click', async () => {
            // Find and validate all Step 1 inputs
            const step1Inputs = step1.querySelectorAll('input, select');
            let isStep1Valid = true;

            for (let input of step1Inputs) {
                if (input.hasAttribute('required') && !input.value) {
                    input.reportValidity();
                    isStep1Valid = false;
                    return;
                }
                if (!input.checkValidity()) {
                    input.reportValidity();
                    isStep1Valid = false;
                    return;
                }
            }

            if (!isStep1Valid) return;

            // Trigger spinner
            btnNextStep.disabled = true;
            nextBtnText.textContent = "Verifying MS Account...";
            nextBtnSpinner.classList.remove('d-none');
            step1Error.classList.add('d-none');

            const emailInput = step1.querySelector('input[name="email"]').value;
            const csrfToken = registerForm.querySelector('input[name="_token"]').value;

            try {
               const response = await fetch('/register/check-email', {
                   method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ email: emailInput })
                });

                const data = await response.json();

                btnNextStep.disabled = false;
                nextBtnText.innerHTML = '<i class="bi bi-arrow-right-circle"></i> Verify &amp; Continue';
                nextBtnSpinner.classList.add('d-none');

                if (data.success) {
                    // Smooth Transition to Step 2
                    step1.classList.add('d-none');
                    step2.classList.remove('d-none');
                } else {
                    step1Error.textContent = data.message || 'Email verification failed.';
                    step1Error.classList.remove('d-none');
                }
            } catch (err) {
                console.error(err);
                btnNextStep.disabled = false;
                nextBtnText.innerHTML = '<i class="bi bi-arrow-right-circle"></i> Verify &amp; Continue';
                nextBtnSpinner.classList.add('d-none');
                step1Error.textContent = 'Connection error occurred. Please check your network and try again.';
                step1Error.classList.remove('d-none');
            }
        });

        // STEP 2 TO STEP 1 fallback (Back Button)
        btnBackStep.addEventListener('click', () => {
            step2.classList.add('d-none');
            step1.classList.remove('d-none');
        });

        // Step 2 Form validation (Password Match)
        registerForm.addEventListener('submit', (e) => {
            const password = document.getElementById('register_password').value;
            const confirmPassword = document.getElementById('register_password_confirmation').value;

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match. Please verify.');
                return;
            }

            // Ensure CAPTCHA verified token is present if official site key exists
            const verifiedTokenInput = document.getElementById('captcha_verified_token');
            if (verifiedTokenInput && !verifiedTokenInput.value) {
                e.preventDefault();
                alert('Please complete the "I am not a robot" security check.');
            }
        });
    });
</script>
    </div>
</div>
<script src="{{ asset('assets/js/main.js') }}"></script>
@include('partials.pwa-installer', ['floating' => true])
</body>
</html>
