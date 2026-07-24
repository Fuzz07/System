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
                    <div class="col-md-5">
                        <label class="form-label-custom">Date of Birth</label>
                        <input type="date" id="dob_input" name="dob" class="form-control-custom" value="{{ old('dob') }}" max="{{ date('Y-m-d', strtotime('-10 years')) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-custom">Age</label>
                        <input type="number" id="age_input" name="age" class="form-control-custom bg-light" value="{{ old('age') }}" readonly tabindex="-1" required>
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
                </div>
                <div class="row g-2 mb-4">
                    <div class="col-md-4">
                        <label class="form-label-custom">Course / Dept</label>
                        <select name="department" class="form-select-custom" required>
                            <option value="">Select Course</option>
                            @foreach(['BEED', 'BSED', 'BSBA', 'BSHM', 'BSIT'] as $dept)
                            <option value="{{ $dept }}" {{ old('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-custom">Student ID</label>
                        <input type="text" name="student_id" class="form-control-custom" pattern="\d{4}-\d{4}" title="Format: YYYY-XXXX" placeholder="YYYY-XXXX" value="{{ old('student_id') }}" required>
                    </div>
                    <div class="col-md-4">
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

            <!-- STEP 1.5: OTP Verification -->
            <div id="step-otp" class="d-none">
                <div id="otp-message" class="alert alert-info mb-3" style="border-radius:var(--radius-sm);font-size:.85rem;line-height:1.5;"></div>

                <div class="mb-4 text-center">
                    <label class="form-label-custom d-block mb-2 text-start">6-Digit Verification Code</label>
                    <input type="text" id="otp_code" class="form-control-custom text-center fw-bold" style="font-size:24px; letter-spacing:8px; max-width:240px; margin:0 auto;" placeholder="000000" maxlength="6" pattern="\d{6}" required>
                </div>

                <div id="otp-error" class="alert alert-danger d-none mb-3" style="border-radius:var(--radius-sm);font-size:.85rem;"></div>

                <div class="d-flex gap-2">
                    <button type="button" id="btn-back-to-step1" class="btn-secondary-custom" style="padding:14px; width:110px; display:flex; align-items:center; justify-content:center; gap:6px; font-weight:600; border-radius:var(--radius-sm); border:1.5px solid #cbd5e1; background:#fff; color:#475569;">
                        <i class="bi bi-arrow-left-circle"></i> Edit Info
                    </button>
                    <button type="button" id="btn-verify-otp" class="btn-primary-custom flex-grow-1 justify-content-center" style="padding:14px; display:flex; align-items:center; gap:8px;">
                        <span id="verify-btn-text">Verify Code</span>
                        <div id="verify-btn-spinner" class="spinner-border spinner-border-sm text-white d-none" role="status"></div>
                    </button>
                </div>
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
        const btnBackToStep1 = document.getElementById('btn-back-to-step1');
        const btnVerifyOtp = document.getElementById('btn-verify-otp');
        
        const step1 = document.getElementById('step-1');
        const stepOtp = document.getElementById('step-otp');
        const step2 = document.getElementById('step-2');
        
        const step1Error = document.getElementById('step-1-error');
        const otpError = document.getElementById('otp-error');
        const otpMessage = document.getElementById('otp-message');
        
        const nextBtnText = document.getElementById('next-btn-text');
        const nextBtnSpinner = document.getElementById('next-btn-spinner');
        const verifyBtnText = document.getElementById('verify-btn-text');
        const verifyBtnSpinner = document.getElementById('verify-btn-spinner');
        
        const registerForm = document.getElementById('registerForm');
        const otpCodeInput = document.getElementById('otp_code');

        if (!btnNextStep) return;

        // STEP 1 TO STEP 1.5: Send OTP & Ask for code
        btnNextStep.addEventListener('click', async () => {
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
                    // Transition to OTP Code verification step
                    otpMessage.innerHTML = '<i class="bi bi-envelope-check-fill text-primary"></i> ' + (data.message || 'Verification code sent.');
                    step1.classList.add('d-none');
                    stepOtp.classList.remove('d-none');
                    otpCodeInput.value = '';
                    otpCodeInput.focus();
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

        // STEP 1.5 TO STEP 2: Verify OTP
        btnVerifyOtp.addEventListener('click', async () => {
            const otpVal = otpCodeInput.value.trim();
            if (otpVal.length !== 6) {
                otpCodeInput.reportValidity();
                return;
            }

            btnVerifyOtp.disabled = true;
            verifyBtnText.textContent = "Verifying Code...";
            verifyBtnSpinner.classList.remove('d-none');
            otpError.classList.add('d-none');

            const emailInput = step1.querySelector('input[name="email"]').value;
            const csrfToken = registerForm.querySelector('input[name="_token"]').value;

            try {
                const response = await fetch('/register/verify-otp', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ email: emailInput, otp: otpVal })
                });

                const data = await response.json();

                btnVerifyOtp.disabled = false;
                verifyBtnText.textContent = "Verify Code";
                verifyBtnSpinner.classList.add('d-none');

                if (data.success) {
                    // Successfully Verified! Move to Password step
                    stepOtp.classList.add('d-none');
                    step2.classList.remove('d-none');
                } else {
                    otpError.textContent = data.message || 'Invalid verification code.';
                    otpError.classList.remove('d-none');
                }
            } catch (err) {
                console.error(err);
                btnVerifyOtp.disabled = false;
                verifyBtnText.textContent = "Verify Code";
                verifyBtnSpinner.classList.add('d-none');
                otpError.textContent = 'Connection error occurred. Please try again.';
                otpError.classList.remove('d-none');
            }
        });

        // Back to Step 1 from OTP
        btnBackToStep1.addEventListener('click', () => {
            stepOtp.classList.add('d-none');
            step1.classList.remove('d-none');
        });

        // Back to Step 1 from Step 2
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

        // Date of Birth - Auto Age Calculator
        const dobInput = document.getElementById('dob_input');
        const ageInput = document.getElementById('age_input');
        if (dobInput && ageInput) {
            dobInput.addEventListener('change', () => {
                if (dobInput.value) {
                    const dob = new Date(dobInput.value);
                    const today = new Date();
                    let age = today.getFullYear() - dob.getFullYear();
                    const monthDiff = today.getMonth() - dob.getMonth();
                    
                    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
                        age--;
                    }
                    ageInput.value = age > 0 ? age : 0;
                } else {
                    ageInput.value = '';
                }
            });
        }
    });
</script>
    </div>
</div>
<script src="{{ asset('assets/js/main.js') }}"></script>
@include('partials.pwa-installer', ['floating' => true])
</body>
</html>
