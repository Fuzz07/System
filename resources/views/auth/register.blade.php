<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.security-guard')
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
    <style>
        /* ── OTP verification step ─────────────────────────────────────────── */
        .otp-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            font-size: .78rem;
            color: #64748b;
        }
        .otp-timer { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-weight: 700; color: #334155; }
        .otp-timer.is-warning { color: var(--warning); }
        .otp-timer.is-expired { color: var(--danger); }
        .otp-input.is-invalid-field {
            border-color: var(--danger) !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, .15) !important;
        }
        .otp-input:disabled { background: #f1f5f9; color: #94a3b8; cursor: not-allowed; }
        .shake-once { animation: fieldShake .38s ease; }
        @keyframes fieldShake {
            0%, 100% { transform: translateX(0); }
            20% { transform: translateX(-7px); }
            40% { transform: translateX(6px); }
            60% { transform: translateX(-4px); }
            80% { transform: translateX(3px); }
        }
        .otp-resend-row { font-size: .8rem; color: #64748b; }
        .btn-resend {
            background: none;
            border: none;
            padding: 0 0 0 4px;
            font-weight: 600;
            font-size: .8rem;
            color: var(--primary);
            cursor: pointer;
        }
        .btn-resend:disabled { color: #94a3b8; cursor: not-allowed; }
        .btn-resend:not(:disabled):hover { text-decoration: underline; }

        /* ── Password strength & requirements (Step 2 only) ────────────────── */
        .pw-panel {
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            border-radius: var(--radius-sm);
            padding: 12px 14px;
        }
        .pw-panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: .76rem;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 6px;
        }
        #pw-strength-text { font-weight: 700; color: #94a3b8; }
        .pw-meter { height: 6px; background: #e2e8f0; border-radius: 99px; overflow: hidden; }
        .pw-meter-bar {
            height: 100%;
            width: 0%;
            border-radius: 99px;
            background: var(--danger);
            transition: width .25s ease, background-color .25s ease;
        }
        .pw-req-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 4px 12px;
            margin-top: 10px;
        }
        @media (max-width: 575.98px) { .pw-req-grid { grid-template-columns: 1fr; } }
        .pw-req {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: .76rem;
            color: #94a3b8;
            transition: color .2s ease;
        }
        .pw-req i { font-size: .9rem; line-height: 1; }
        .pw-req.is-met { color: var(--success); }
        .pw-req.is-unmet-touched { color: var(--danger); }
        .form-control-custom.is-invalid-field {
            border-color: var(--danger) !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, .15) !important;
        }
        .form-control-custom.is-valid-field { border-color: var(--success) !important; }
        .field-hint { font-size: .74rem; margin-top: 5px; display: flex; align-items: center; gap: 5px; }
        .field-hint.is-error { color: var(--danger); }
        .field-hint.is-ok { color: var(--success); }
        .btn-primary-custom:disabled { opacity: .55; cursor: not-allowed; }
    </style>
</head>
<body>
<div class="login-page">
    @include('partials.auth-backdrop')

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

                <div class="mb-2 text-center">
                    <label for="otp_code" class="form-label-custom d-block mb-2 text-start">6-Digit Verification Code</label>
                    <input type="text" id="otp_code" class="form-control-custom text-center fw-bold otp-input"
                           style="font-size:24px; letter-spacing:8px; max-width:240px; margin:0 auto;"
                           placeholder="000000" maxlength="6" inputmode="numeric" autocomplete="one-time-code"
                           spellcheck="false" aria-describedby="otp-status">
                </div>

                <div class="otp-meta mb-3" id="otp-status">
                    <span><i class="bi bi-clock-history"></i> Code expires in <span id="otp-countdown" class="otp-timer">03:00</span></span>
                    <span id="otp-attempts"></span>
                </div>

                <div id="otp-error" class="alert alert-danger d-none mb-3" role="alert" style="border-radius:var(--radius-sm);font-size:.85rem;">
                    <i class="bi bi-exclamation-triangle-fill"></i> <span id="otp-error-text"></span>
                </div>

                <div id="otp-resent" class="alert alert-success d-none mb-3" role="status" style="border-radius:var(--radius-sm);font-size:.85rem;">
                    <i class="bi bi-envelope-check-fill"></i> <span id="otp-resent-text"></span>
                </div>

                <div class="d-flex gap-2">
                    <button type="button" id="btn-back-to-step1" class="btn-secondary-custom" style="padding:14px; width:110px; display:flex; align-items:center; justify-content:center; gap:6px; font-weight:600; border-radius:var(--radius-sm); border:1.5px solid #cbd5e1; background:#fff; color:#475569;">
                        <i class="bi bi-arrow-left-circle"></i> Edit Info
                    </button>
                    <button type="button" id="btn-verify-otp" class="btn-primary-custom flex-grow-1 justify-content-center" style="padding:14px; display:flex; align-items:center; gap:8px;" disabled>
                        <span id="verify-btn-text">Verify Code</span>
                        <div id="verify-btn-spinner" class="spinner-border spinner-border-sm text-white d-none" role="status"></div>
                    </button>
                </div>

                <div class="text-center mt-3 otp-resend-row">
                    <span>Didn't receive the code?</span>
                    <button type="button" id="btn-resend-otp" class="btn-resend" disabled>
                        <span id="resend-btn-text">Resend code</span>
                    </button>
                </div>
            </div>

            <!-- STEP 2: Password Creation & CAPTCHA Verification -->
            <div id="step-2" class="d-none">
                <div class="alert alert-success mb-3" style="border-radius:var(--radius-sm);font-size:.85rem;">
                    <i class="bi bi-check-circle-fill"></i> MS Account verified successfully! Please secure your account by creating a password.
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label-custom" for="register_password">Password</label>
                        <div style="position: relative;">
                            <input type="password" name="password" id="register_password" class="form-control-custom" minlength="8" autocomplete="new-password" aria-describedby="pw-panel" style="padding-right: 44px;">
                            <button type="button" onclick="togglePasswordVisibility('register_password', this)" style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); background: none; border: none; padding: 0; color: #64748b; font-size: 1.15rem; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 10;">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-custom" for="register_password_confirmation">Confirm Password</label>
                        <div style="position: relative;">
                            <input type="password" name="password_confirmation" id="register_password_confirmation" class="form-control-custom" minlength="8" autocomplete="new-password" aria-describedby="pw-match-hint" style="padding-right: 44px;">
                            <button type="button" onclick="togglePasswordVisibility('register_password_confirmation', this)" style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); background: none; border: none; padding: 0; color: #64748b; font-size: 1.15rem; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 10;">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div id="pw-match-hint" class="field-hint d-none"></div>
                    </div>
                </div>

                <div class="pw-panel mb-3" id="pw-panel">
                    <div class="pw-panel-head">
                        <span><i class="bi bi-shield-lock"></i> Password strength</span>
                        <span id="pw-strength-text">Enter a password</span>
                    </div>
                    <div class="pw-meter" role="progressbar" aria-labelledby="pw-strength-text" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                        <div id="pw-meter-bar" class="pw-meter-bar"></div>
                    </div>
                    <div class="pw-req-grid">
                        <div class="pw-req" data-req="length"><i class="bi bi-circle"></i> At least 8 characters</div>
                        <div class="pw-req" data-req="letter"><i class="bi bi-circle"></i> Contains a letter</div>
                        <div class="pw-req" data-req="number"><i class="bi bi-circle"></i> Contains a number</div>
                        <div class="pw-req" data-req="match"><i class="bi bi-circle"></i> Both passwords match</div>
                    </div>
                </div>

                <div id="password-error" class="alert alert-danger d-none mb-3" role="alert" style="border-radius:var(--radius-sm);font-size:.85rem;">
                    <i class="bi bi-exclamation-triangle-fill"></i> <span id="password-error-text"></span>
                </div>

                @include('partials.captcha')

                <div class="d-flex gap-2">
                    <button type="button" id="btn-back-step" class="btn-secondary-custom" style="padding:14px; width:110px; display:flex; align-items:center; justify-content:center; gap:6px; font-weight:600; border-radius:var(--radius-sm); border:1.5px solid #cbd5e1; background:#fff; color:#475569;">
                        <i class="bi bi-arrow-left-circle"></i> Back
                    </button>
                    <button type="submit" id="btn-complete-registration" class="btn-primary-custom flex-grow-1 justify-content-center" style="padding:14px;" disabled>
                        <i class="bi bi-person-plus"></i> Complete Registration
                    </button>
                </div>
            </div>
        </form>

        <div class="text-center mt-3">
            <a href="{{ route('login.student') }}" class="text-muted" style="font-size:.82rem;text-decoration:none;">
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
        const btnResendOtp = document.getElementById('btn-resend-otp');
        const btnComplete = document.getElementById('btn-complete-registration');

        const step1 = document.getElementById('step-1');
        const stepOtp = document.getElementById('step-otp');
        const step2 = document.getElementById('step-2');

        const step1Error = document.getElementById('step-1-error');
        const otpError = document.getElementById('otp-error');
        const otpErrorText = document.getElementById('otp-error-text');
        const otpResent = document.getElementById('otp-resent');
        const otpResentText = document.getElementById('otp-resent-text');
        const otpMessage = document.getElementById('otp-message');
        const otpCountdown = document.getElementById('otp-countdown');
        const otpAttempts = document.getElementById('otp-attempts');

        const nextBtnText = document.getElementById('next-btn-text');
        const nextBtnSpinner = document.getElementById('next-btn-spinner');
        const verifyBtnText = document.getElementById('verify-btn-text');
        const verifyBtnSpinner = document.getElementById('verify-btn-spinner');
        const resendBtnText = document.getElementById('resend-btn-text');

        const registerForm = document.getElementById('registerForm');
        const otpCodeInput = document.getElementById('otp_code');

        const passwordInput = document.getElementById('register_password');
        const confirmInput = document.getElementById('register_password_confirmation');
        const pwMeterBar = document.getElementById('pw-meter-bar');
        const pwMeter = pwMeterBar ? pwMeterBar.parentElement : null;
        const pwStrengthText = document.getElementById('pw-strength-text');
        const pwMatchHint = document.getElementById('pw-match-hint');
        const passwordError = document.getElementById('password-error');
        const passwordErrorText = document.getElementById('password-error-text');
        const pwReqNodes = document.querySelectorAll('#step-2 .pw-req');

        const OTP_LIFETIME_SECONDS = 180;
        let expiryTimerId = null;
        let cooldownTimerId = null;
        let otpLocked = false;

        if (!btnNextStep) return;

        const csrfToken = () => registerForm.querySelector('input[name="_token"]').value;
        const currentEmail = () => step1.querySelector('input[name="email"]').value.trim();

        // ───────────────────────────────────────────────────────────────────
        //  OTP STEP HELPERS
        // ───────────────────────────────────────────────────────────────────
        function showOtpError(message, shake = true) {
            otpResent.classList.add('d-none');
            otpErrorText.textContent = message;
            otpError.classList.remove('d-none');
            otpCodeInput.classList.add('is-invalid-field');
            if (shake) {
                otpCodeInput.classList.remove('shake-once');
                void otpCodeInput.offsetWidth; // restart the animation
                otpCodeInput.classList.add('shake-once');
            }
        }

        function clearOtpError() {
            otpError.classList.add('d-none');
            otpErrorText.textContent = '';
            otpCodeInput.classList.remove('is-invalid-field', 'shake-once');
        }

        function showOtpNotice(message) {
            otpResentText.textContent = message;
            otpResent.classList.remove('d-none');
        }

        function setAttemptsLeft(remaining) {
            if (remaining === null || remaining === undefined) {
                otpAttempts.textContent = '';
                otpAttempts.style.color = '';
                return;
            }
            otpAttempts.textContent = remaining + ' attempt' + (remaining === 1 ? '' : 's') + ' remaining';
            otpAttempts.style.color = remaining <= 2 ? 'var(--danger)' : '#64748b';
        }

        function setOtpLocked(locked) {
            otpLocked = locked;
            otpCodeInput.disabled = locked;
            if (locked) {
                stopExpiryCountdown();
                otpCountdown.textContent = 'EXPIRED';
                otpCountdown.classList.remove('is-warning');
                otpCountdown.classList.add('is-expired');
                btnVerifyOtp.disabled = true;
                // A new code can be requested right away once the old one is dead.
                stopResendCooldown();
                enableResend();
            } else {
                syncVerifyButton();
            }
        }

        function syncVerifyButton() {
            btnVerifyOtp.disabled = otpLocked || otpCodeInput.value.trim().length !== 6;
        }

        function stopExpiryCountdown() {
            if (expiryTimerId) { clearInterval(expiryTimerId); expiryTimerId = null; }
        }

        function startExpiryCountdown(expiresAtSeconds) {
            stopExpiryCountdown();
            const deadline = (expiresAtSeconds ? expiresAtSeconds * 1000 : Date.now() + OTP_LIFETIME_SECONDS * 1000);

            const tick = () => {
                const remaining = Math.max(0, Math.round((deadline - Date.now()) / 1000));
                if (remaining <= 0) {
                    stopExpiryCountdown();
                    otpCountdown.textContent = 'EXPIRED';
                    otpCountdown.classList.remove('is-warning');
                    otpCountdown.classList.add('is-expired');
                    setOtpLocked(true);
                    showOtpError('This verification code has expired. Please request a new code.', false);
                    return;
                }
                const mins = Math.floor(remaining / 60);
                const secs = remaining % 60;
                otpCountdown.textContent = String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
                otpCountdown.classList.toggle('is-warning', remaining <= 30);
                otpCountdown.classList.remove('is-expired');
            };

            tick();
            expiryTimerId = setInterval(tick, 1000);
        }

        function stopResendCooldown() {
            if (cooldownTimerId) { clearInterval(cooldownTimerId); cooldownTimerId = null; }
        }

        function enableResend() {
            btnResendOtp.disabled = false;
            resendBtnText.textContent = 'Resend code';
        }

        function startResendCooldown(seconds) {
            stopResendCooldown();
            let remaining = Math.max(0, parseInt(seconds, 10) || 0);
            if (remaining === 0) { enableResend(); return; }

            btnResendOtp.disabled = true;
            const tick = () => {
                if (remaining <= 0) {
                    stopResendCooldown();
                    enableResend();
                    return;
                }
                resendBtnText.textContent = 'Resend code in ' + remaining + 's';
                remaining--;
            };
            tick();
            cooldownTimerId = setInterval(tick, 1000);
        }

        // Digits only, and keep the Verify button in sync with what is typed.
        otpCodeInput.addEventListener('input', () => {
            const cleaned = otpCodeInput.value.replace(/\D/g, '').slice(0, 6);
            if (cleaned !== otpCodeInput.value) otpCodeInput.value = cleaned;
            if (otpError && !otpError.classList.contains('d-none')) clearOtpError();
            syncVerifyButton();
        });

        otpCodeInput.addEventListener('paste', (e) => {
            const pasted = (e.clipboardData || window.clipboardData).getData('text') || '';
            const digits = pasted.replace(/\D/g, '').slice(0, 6);
            if (digits) {
                e.preventDefault();
                otpCodeInput.value = digits;
                clearOtpError();
                syncVerifyButton();
            }
        });

        otpCodeInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (!btnVerifyOtp.disabled) btnVerifyOtp.click();
            }
        });

        // ───────────────────────────────────────────────────────────────────
        //  STEP 1 → OTP STEP: validate details and send the first code
        // ───────────────────────────────────────────────────────────────────
        btnNextStep.addEventListener('click', async (e) => {
            if (e) e.preventDefault();
            const step1Inputs = step1.querySelectorAll('input, select');

            for (let input of step1Inputs) {
                if (input.hasAttribute('required') && !input.value) {
                    input.reportValidity();
                    return;
                }
                if (!input.checkValidity()) {
                    input.reportValidity();
                    return;
                }
            }

            btnNextStep.disabled = true;
            nextBtnText.textContent = "Verifying MS Account...";
            nextBtnSpinner.classList.remove('d-none');
            step1Error.classList.add('d-none');

            try {
                const response = await fetch('/register/check-email', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken()
                    },
                    body: JSON.stringify({ email: currentEmail() })
                });

                const data = await response.json();

                btnNextStep.disabled = false;
                nextBtnText.innerHTML = '<i class="bi bi-arrow-right-circle"></i> Verify &amp; Continue';
                nextBtnSpinner.classList.add('d-none');

                if (data.success) {
                    otpMessage.innerHTML = '<i class="bi bi-envelope-check-fill text-primary"></i> ' + (data.message || 'Verification code sent.');
                    step1.classList.add('d-none');
                    stepOtp.classList.remove('d-none');

                    otpCodeInput.value = '';
                    otpCodeInput.disabled = false;
                    otpLocked = false;
                    clearOtpError();
                    otpResent.classList.add('d-none');
                    setAttemptsLeft(null);
                    syncVerifyButton();
                    startExpiryCountdown(data.expires_at);
                    startResendCooldown(data.resend_available_in);
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

        // ───────────────────────────────────────────────────────────────────
        //  OTP STEP → STEP 2: verify the code
        // ───────────────────────────────────────────────────────────────────
        btnVerifyOtp.addEventListener('click', async (e) => {
            if (e) e.preventDefault();
            const otpVal = otpCodeInput.value.trim();

            if (otpLocked) {
                showOtpError('This code is no longer valid. Please request a new code.');
                return;
            }
            if (otpVal.length !== 6) {
                showOtpError('Please enter the complete 6-digit code sent to your school email.');
                otpCodeInput.focus();
                return;
            }

            btnVerifyOtp.disabled = true;
            verifyBtnText.textContent = "Verifying Code...";
            verifyBtnSpinner.classList.remove('d-none');
            clearOtpError();
            otpResent.classList.add('d-none');

            try {
                const response = await fetch('/register/verify-otp', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken()
                    },
                    body: JSON.stringify({ email: currentEmail(), otp: otpVal })
                });

                const data = await response.json();

                verifyBtnText.textContent = "Verify Code";
                verifyBtnSpinner.classList.add('d-none');

                if (data.success) {
                    stopExpiryCountdown();
                    stopResendCooldown();
                    stepOtp.classList.add('d-none');
                    step2.classList.remove('d-none');
                    setStepTwoActive(true);
                    passwordInput.focus();
                    return;
                }

                showOtpError(data.message || 'Invalid verification code.');
                otpCodeInput.select();

                if (typeof data.attempts_left === 'number') {
                    setAttemptsLeft(data.attempts_left);
                }
                if (data.locked || data.expired || data.can_resend) {
                    setAttemptsLeft(null);
                    setOtpLocked(true);
                } else {
                    syncVerifyButton();
                }
            } catch (err) {
                console.error(err);
                verifyBtnText.textContent = "Verify Code";
                verifyBtnSpinner.classList.add('d-none');
                syncVerifyButton();
                showOtpError('Connection error occurred. Please try again.', false);
            }
        });

        // ───────────────────────────────────────────────────────────────────
        //  RESEND CODE
        // ───────────────────────────────────────────────────────────────────
        btnResendOtp.addEventListener('click', async (e) => {
            if (e) e.preventDefault();
            if (btnResendOtp.disabled) return;

            btnResendOtp.disabled = true;
            resendBtnText.textContent = 'Sending...';
            clearOtpError();
            otpResent.classList.add('d-none');

            try {
                const response = await fetch('/register/resend-otp', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken()
                    },
                    body: JSON.stringify({ email: currentEmail() })
                });

                const data = await response.json();

                if (data.success) {
                    otpCodeInput.disabled = false;
                    otpCodeInput.value = '';
                    otpLocked = false;
                    otpCountdown.classList.remove('is-expired');
                    setAttemptsLeft(null);
                    syncVerifyButton();
                    startExpiryCountdown(data.expires_at);
                    startResendCooldown(data.resend_available_in);

                    let notice = data.message || 'A new verification code has been sent.';
                    if (typeof data.resends_left === 'number') {
                        notice += ' You may request ' + data.resends_left + ' more code' + (data.resends_left === 1 ? '' : 's') + '.';
                    }
                    showOtpNotice(notice);
                    otpCodeInput.focus();
                } else {
                    showOtpError(data.message || 'Could not resend the verification code.', false);
                    if (data.restart) {
                        // The verification session is finished — only "Edit Info" can recover it.
                        setOtpLocked(true);
                        stopResendCooldown();
                        btnResendOtp.disabled = true;
                        resendBtnText.textContent = 'Resend unavailable';
                    } else {
                        startResendCooldown(data.resend_available_in || 60);
                    }
                }
            } catch (err) {
                console.error(err);
                showOtpError('Connection error occurred. Please try again.', false);
                enableResend();
            }
        });

        // Back to Step 1 from OTP
        btnBackToStep1.addEventListener('click', () => {
            stopExpiryCountdown();
            stopResendCooldown();
            stepOtp.classList.add('d-none');
            step1.classList.remove('d-none');
        });

        // Back to Step 1 from Step 2
        btnBackStep.addEventListener('click', () => {
            setStepTwoActive(false);
            step2.classList.add('d-none');
            step1.classList.remove('d-none');
        });

        // ───────────────────────────────────────────────────────────────────
        //  STEP 2: PASSWORD VALIDATION (scoped to the password section only)
        // ───────────────────────────────────────────────────────────────────
        const STRENGTH_LEVELS = [
            { label: 'Very weak', color: '#ef4444', width: 15 },
            { label: 'Weak',      color: '#ef4444', width: 30 },
            { label: 'Fair',      color: '#f59e0b', width: 50 },
            { label: 'Good',      color: '#3b82f6', width: 72 },
            { label: 'Strong',    color: '#10b981', width: 88 },
            { label: 'Very strong', color: '#10b981', width: 100 }
        ];

        // Mirrors the server rules: min 8 characters, at least one letter and one number.
        function passwordChecks() {
            const pw = passwordInput.value;
            const confirm = confirmInput.value;
            return {
                length: pw.length >= 8,
                letter: /[a-zA-Z]/.test(pw),
                number: /\d/.test(pw),
                match: pw.length > 0 && pw === confirm
            };
        }

        function strengthScore(pw) {
            if (!pw) return -1;
            let score = 0;
            if (pw.length >= 8) score++;
            if (pw.length >= 12) score++;
            if (/[a-z]/.test(pw) && /[A-Z]/.test(pw)) score++;
            if (/\d/.test(pw)) score++;
            if (/[^A-Za-z0-9]/.test(pw)) score++;
            // A password that fails the baseline rules can never read above "Weak".
            if (pw.length < 8 || !/[a-zA-Z]/.test(pw) || !/\d/.test(pw)) score = Math.min(score, 1);
            return score;
        }

        function isPasswordSectionValid() {
            const checks = passwordChecks();
            return checks.length && checks.letter && checks.number && checks.match;
        }

        function evaluatePasswordSection() {
            if (!passwordInput) return false;

            const pw = passwordInput.value;
            const confirm = confirmInput.value;
            const checks = passwordChecks();

            // Requirement checklist
            pwReqNodes.forEach((node) => {
                const key = node.getAttribute('data-req');
                const met = !!checks[key];
                const touched = key === 'match' ? confirm.length > 0 : pw.length > 0;
                const icon = node.querySelector('i');

                node.classList.toggle('is-met', met);
                node.classList.toggle('is-unmet-touched', !met && touched);
                icon.className = met
                    ? 'bi bi-check-circle-fill'
                    : (touched ? 'bi bi-x-circle-fill' : 'bi bi-circle');
            });

            // Strength meter
            const score = strengthScore(pw);
            if (score < 0) {
                pwMeterBar.style.width = '0%';
                pwMeterBar.style.backgroundColor = '#e2e8f0';
                pwStrengthText.textContent = 'Enter a password';
                pwStrengthText.style.color = '#94a3b8';
                if (pwMeter) pwMeter.setAttribute('aria-valuenow', 0);
            } else {
                const level = STRENGTH_LEVELS[Math.min(score, STRENGTH_LEVELS.length - 1)];
                pwMeterBar.style.width = level.width + '%';
                pwMeterBar.style.backgroundColor = level.color;
                pwStrengthText.textContent = level.label;
                pwStrengthText.style.color = level.color;
                if (pwMeter) pwMeter.setAttribute('aria-valuenow', level.width);
            }

            // Password field state
            passwordInput.classList.toggle('is-valid-field', checks.length && checks.letter && checks.number);
            passwordInput.classList.toggle('is-invalid-field', pw.length > 0 && !(checks.length && checks.letter && checks.number));

            // Confirm field state + inline hint
            if (confirm.length === 0) {
                confirmInput.classList.remove('is-valid-field', 'is-invalid-field');
                pwMatchHint.classList.add('d-none');
                pwMatchHint.textContent = '';
            } else if (checks.match) {
                confirmInput.classList.add('is-valid-field');
                confirmInput.classList.remove('is-invalid-field');
                pwMatchHint.className = 'field-hint is-ok';
                pwMatchHint.innerHTML = '<i class="bi bi-check-circle-fill"></i> Passwords match';
            } else {
                confirmInput.classList.add('is-invalid-field');
                confirmInput.classList.remove('is-valid-field');
                pwMatchHint.className = 'field-hint is-error';
                pwMatchHint.innerHTML = '<i class="bi bi-exclamation-circle-fill"></i> Passwords do not match';
            }

            const valid = isPasswordSectionValid();
            if (btnComplete) btnComplete.disabled = !valid;
            if (valid) hidePasswordError();

            return valid;
        }

        function showPasswordError(message) {
            passwordErrorText.textContent = message;
            passwordError.classList.remove('d-none');
        }

        function hidePasswordError() {
            passwordError.classList.add('d-none');
            passwordErrorText.textContent = '';
        }

        // The password fields only become real form requirements once Step 2 is on
        // screen, so the browser never validates a hidden section.
        function setStepTwoActive(active) {
            if (!passwordInput) return;
            if (active) {
                passwordInput.setAttribute('required', 'required');
                confirmInput.setAttribute('required', 'required');
                evaluatePasswordSection();
            } else {
                passwordInput.removeAttribute('required');
                confirmInput.removeAttribute('required');
            }
        }

        if (passwordInput && confirmInput) {
            ['input', 'blur', 'paste'].forEach((evt) => {
                passwordInput.addEventListener(evt, () => setTimeout(evaluatePasswordSection, 0));
                confirmInput.addEventListener(evt, () => setTimeout(evaluatePasswordSection, 0));
            });
            setStepTwoActive(false);
            evaluatePasswordSection();
        }

        // ───────────────────────────────────────────────────────────────────
        //  FINAL SUBMIT
        // ───────────────────────────────────────────────────────────────────
        registerForm.addEventListener('submit', (e) => {
            // Never allow a submit before the password step is reached.
            if (step2.classList.contains('d-none')) {
                e.preventDefault();
                return false;
            }

            if (!evaluatePasswordSection()) {
                e.preventDefault();
                const checks = passwordChecks();
                if (!checks.length || !checks.letter || !checks.number) {
                    showPasswordError('Your password must be at least 8 characters long and include both a letter and a number.');
                    passwordInput.focus();
                } else {
                    showPasswordError('The passwords you entered do not match. Please re-enter them.');
                    confirmInput.focus();
                }
                return false;
            }

            // Ensure CAPTCHA verified token is present if official site key exists
            const verifiedTokenInput = document.getElementById('captcha_verified_token');
            if (verifiedTokenInput && !verifiedTokenInput.value) {
                e.preventDefault();
                showPasswordError('Please complete the "I am not a robot" security check before continuing.');
                return false;
            }

            hidePasswordError();
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
