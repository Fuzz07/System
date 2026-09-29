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
    <link href="{{ asset('assets/css/style.css') }}?v={{ @filemtime(public_path('assets/css/style.css')) ?: 1 }}" rel="stylesheet">
    <link href="{{ asset('assets/css/dialogs.css') }}?v={{ @filemtime(public_path('assets/css/dialogs.css')) ?: 1 }}" rel="stylesheet">
    <style>
        /* ── Password strength & requirements (Step 2) ─────────────────────── */
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
        .form-control-custom.is-invalid-field,
        .form-select-custom.is-invalid-field {
            border-color: var(--danger) !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, .15) !important;
        }
        .form-control-custom.is-valid-field,
        .form-select-custom.is-valid-field { border-color: var(--success) !important; }
        .field-hint { font-size: .74rem; margin-top: 5px; display: flex; align-items: center; gap: 5px; color: #64748b; }
        .field-hint.is-error { color: var(--danger); }
        .field-hint.is-ok { color: var(--success); }
        .btn-primary-custom:disabled { opacity: .55; cursor: not-allowed; }

        /* Registration-specific layout */
        .registration-page {
            align-items: center;
            overflow-y: auto;
            padding: 28px;
        }
        .registration-shell {
            position: relative;
            z-index: 2;
            display: grid;
            grid-template-columns: minmax(330px, .82fr) minmax(590px, 1.18fr);
            width: min(1180px, 100%);
            min-height: 720px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, .18);
            border-radius: 30px;
            background: rgba(255, 255, 255, .96);
            box-shadow: 0 35px 90px rgba(2, 6, 23, .52);
        }
        .registration-visual {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-width: 0;
            padding: 34px;
            isolation: isolate;
            color: #fff;
            background: #071a3e;
        }
        .registration-visual::before {
            content: '';
            position: absolute;
            inset: 0;
            z-index: -2;
            background:
                linear-gradient(180deg, rgba(4, 18, 48, .2), rgba(4, 18, 48, .95)),
                url('{{ asset('assets/images/registration-visual-v1.jpg') }}') center / cover no-repeat;
        }
        .registration-visual::after {
            content: '';
            position: absolute;
            inset: 0;
            z-index: -1;
            background: radial-gradient(circle at 75% 20%, rgba(45, 212, 191, .22), transparent 35%);
        }
        .registration-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .registration-brand-logo {
            width: 54px;
            height: 54px;
            padding: 5px;
            object-fit: contain;
            border-radius: 16px;
            background: rgba(255, 255, 255, .94);
            box-shadow: 0 12px 28px rgba(0, 0, 0, .24);
        }
        .registration-brand strong { display: block; font-size: .96rem; letter-spacing: .02em; }
        .registration-brand span { display: block; margin-top: 2px; color: rgba(255, 255, 255, .68); font-size: .72rem; }
        .registration-visual-copy { max-width: 360px; margin-top: auto; }
        .registration-visual-kicker {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 14px;
            padding: 7px 11px;
            border: 1px solid rgba(94, 234, 212, .35);
            border-radius: 999px;
            background: rgba(15, 118, 110, .2);
            color: #99f6e4;
            font-size: .72rem;
            font-weight: 700;
        }
        .registration-visual h2 {
            margin: 0 0 12px;
            font-size: clamp(1.65rem, 2.5vw, 2.35rem);
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -.04em;
        }
        .registration-visual p { margin: 0; color: rgba(226, 232, 240, .78); font-size: .9rem; line-height: 1.65; }
        .registration-trust-list {
            display: grid;
            gap: 9px;
            margin: 22px 0 0;
            padding: 0;
            list-style: none;
        }
        .registration-trust-list li {
            display: flex;
            align-items: center;
            gap: 9px;
            color: #e2e8f0;
            font-size: .8rem;
            font-weight: 600;
        }
        .registration-trust-list i {
            display: inline-grid;
            width: 25px;
            height: 25px;
            place-items: center;
            border-radius: 8px;
            background: rgba(45, 212, 191, .16);
            color: #5eead4;
        }
        .registration-form-panel {
            min-width: 0;
            padding: 38px 44px 30px;
            color: #0f172a;
            background: rgba(255, 255, 255, .98);
        }
        .registration-mobile-brand { display: none; }
        .registration-eyebrow {
            display: flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 7px;
            color: #4f46e5;
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .11em;
            text-transform: uppercase;
        }
        .registration-title { margin: 0; color: #0f172a; font-size: 1.8rem; font-weight: 850; letter-spacing: -.035em; }
        .registration-subtitle { margin: 7px 0 0; color: #64748b; font-size: .87rem; }
        .registration-steps {
            position: relative;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            margin: 25px 0 28px;
            padding: 0;
            list-style: none;
        }
        .registration-steps::before {
            content: '';
            position: absolute;
            top: 17px;
            right: 25%;
            left: 25%;
            height: 2px;
            background: #e2e8f0;
        }
        .registration-step { position: relative; z-index: 1; text-align: center; color: #94a3b8; font-size: .69rem; font-weight: 700; }
        .registration-step-number {
            display: grid;
            width: 34px;
            height: 34px;
            margin: 0 auto 7px;
            place-items: center;
            border: 2px solid #e2e8f0;
            border-radius: 50%;
            background: #fff;
            color: #94a3b8;
            transition: .2s ease;
        }
        .registration-step.is-active { color: #4338ca; }
        .registration-step.is-active .registration-step-number {
            border-color: #4f46e5;
            background: #4f46e5;
            color: #fff;
            box-shadow: 0 0 0 5px rgba(79, 70, 229, .1);
        }
        .registration-step.is-complete { color: #0f766e; }
        .registration-step.is-complete .registration-step-number { border-color: #14b8a6; background: #ecfdf5; color: #0f766e; }
        .form-section-heading {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 2px 0 14px;
            color: #1e293b;
            font-size: .82rem;
            font-weight: 800;
        }
        .form-section-heading span {
            display: grid;
            width: 30px;
            height: 30px;
            place-items: center;
            border-radius: 9px;
            background: #eef2ff;
            color: #4f46e5;
        }
        .registration-form-panel .form-label-custom { margin-bottom: 6px; color: #334155; font-size: .77rem; }
        .registration-form-panel .form-control-custom,
        .registration-form-panel .form-select-custom {
            min-height: 44px;
            padding: 10px 13px;
            border-color: #dbe3ef;
            background: #f8fafc;
        }
        .registration-form-panel .form-control-custom:focus,
        .registration-form-panel .form-select-custom:focus { border-color: #6366f1; background: #fff; }
        .registration-form-panel .btn-primary-custom {
            min-height: 48px;
            border-radius: 13px;
            background: linear-gradient(135deg, #4f46e5, #2563eb);
            box-shadow: 0 10px 24px rgba(79, 70, 229, .23);
        }
        .registration-form-panel .btn-primary-custom:hover { background: linear-gradient(135deg, #4338ca, #1d4ed8); }
        .registration-form-panel .alert { border: 0; }
        .registration-error {
            display: flex;
            align-items: flex-start;
            gap: 11px;
            padding: 13px 14px;
            border-left: 4px solid #ef4444 !important;
            background: #fff1f2;
            color: #9f1239;
        }
        .registration-error > i { margin-top: 1px; font-size: 1rem; }
        .registration-error-copy { flex: 1; min-width: 0; }
        .registration-error-actions { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 9px; }
        .registration-error-actions a {
            padding: 6px 9px;
            border-radius: 7px;
            background: rgba(190, 18, 60, .09);
            color: #9f1239;
            font-size: .72rem;
            font-weight: 800;
            text-decoration: none;
        }
        .registration-signin { margin-top: 21px; text-align: center; color: #64748b; font-size: .8rem; }
        .registration-signin a { color: #4338ca; font-weight: 800; text-decoration: none; }
        .registration-signin a:hover { text-decoration: underline; }

        @media (max-width: 991.98px) {
            .registration-page { align-items: flex-start; padding: 18px; }
            .registration-shell { grid-template-columns: 1fr; width: min(720px, 100%); min-height: auto; margin: auto; }
            .registration-visual { min-height: 205px; padding: 24px 28px; }
            .registration-visual::before { background-position: center 58%; }
            .registration-visual-copy { margin-top: 40px; }
            .registration-visual-copy h2 { max-width: 480px; font-size: 1.55rem; }
            .registration-visual-copy p, .registration-trust-list { display: none; }
            .registration-form-panel { padding: 32px 34px 28px; }
        }
        @media (max-width: 575.98px) {
            .registration-page { padding: 0; background: #fff; }
            .registration-page::before, .registration-page::after, .registration-page > .auth-bg { display: none; }
            .registration-shell { border: 0; border-radius: 0; box-shadow: none; }
            .registration-visual { display: none; }
            .registration-form-panel { padding: 22px 18px 28px; }
            .registration-mobile-brand { display: flex; align-items: center; gap: 10px; margin-bottom: 24px; }
            .registration-mobile-brand img { width: 42px; height: 42px; object-fit: contain; }
            .registration-mobile-brand strong { display: block; color: #0f172a; font-size: .82rem; }
            .registration-mobile-brand span { display: block; color: #64748b; font-size: .68rem; }
            .registration-title { font-size: 1.55rem; }
            .registration-steps { margin: 22px 0 25px; }
            .registration-step-label { display: none; }
        }
    </style>
</head>
<body>
<div class="login-page registration-page">
    @include('partials.auth-backdrop')

    <main class="registration-shell">
        <aside class="registration-visual" aria-label="SSC student portal benefits">
            <div class="registration-brand">
                <img class="registration-brand-logo" src="{{ asset('assets/images/ssc_logo.png') }}" alt="SSC logo">
                <div>
                    <strong>SSC Transparency Portal</strong>
                    <span>Madridejos Community College</span>
                </div>
            </div>

            <div class="registration-visual-copy">
                <div class="registration-visual-kicker"><i class="bi bi-shield-check"></i> Direct student registration</div>
                <h2>Your voice. Your council. Your campus.</h2>
                <p>Create your student account to access council updates, transparent budget information, elections, and student services.</p>
                <ul class="registration-trust-list">
                    <li><i class="bi bi-google"></i> Sign up using your Gmail account</li>
                    <li><i class="bi bi-lock"></i> Protected by modern security checks</li>
                    <li><i class="bi bi-bar-chart"></i> Built for open and accountable student governance</li>
                </ul>
            </div>
        </aside>

        <section class="registration-form-panel">
            <div class="registration-mobile-brand">
                <img src="{{ asset('assets/images/ssc_logo.png') }}" alt="SSC logo">
                <div><strong>SSC Transparency Portal</strong><span>Madridejos Community College</span></div>
            </div>

            <header>
                <div class="registration-eyebrow"><i class="bi bi-person-badge"></i> Student access</div>
                <h1 class="registration-title">Create your account</h1>
                <p class="registration-subtitle">Complete the two simple steps below. It only takes a minute.</p>
            </header>

            <ol class="registration-steps" aria-label="Registration progress">
                <li class="registration-step is-active" data-stage="1"><span class="registration-step-number">1</span><span class="registration-step-label">Student details</span></li>
                <li class="registration-step" data-stage="2"><span class="registration-step-number">2</span><span class="registration-step-label">Account security</span></li>
            </ol>

        @if($errors->any())
        <div class="alert registration-error mb-3" role="alert">
            <i class="bi bi-exclamation-octagon-fill"></i>
            <div class="registration-error-copy">
                <strong>We could not complete your registration.</strong>
                <ul class="mb-0 mt-1 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        </div>
        @endif

        @php
            $hasAccountErrors = $errors->has('email') || $errors->has('password') || $errors->has('captcha_token') || $errors->has('captcha_verified_token');
        @endphp

        <form method="POST" action="{{ route('register.submit') }}" id="registerForm" novalidate>
            @csrf
            <div style="display:none !important;" aria-hidden="true">
                <input type="text" name="website_url" tabindex="-1" autocomplete="off">
            </div>

            <!-- STEP 1: Student details -->
            <div id="step-details" class="{{ $hasAccountErrors ? 'd-none' : '' }}">
                <div class="form-section-heading"><span><i class="bi bi-person-vcard"></i></span> Personal information</div>
                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <label class="form-label-custom" for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" class="form-control-custom" value="{{ old('first_name') }}" minlength="2" maxlength="100" autocomplete="given-name" required autofocus>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-custom" for="middle_name">Middle Name <span class="text-muted fw-normal">(optional)</span></label>
                        <input type="text" id="middle_name" name="middle_name" class="form-control-custom" value="{{ old('middle_name') }}" maxlength="100" autocomplete="additional-name">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-custom" for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" class="form-control-custom" value="{{ old('last_name') }}" minlength="2" maxlength="100" autocomplete="family-name" required>
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-5">
                        <label class="form-label-custom" for="dob_input">Date of Birth</label>
                        <input type="date" id="dob_input" name="dob" class="form-control-custom" value="{{ old('dob') }}" min="{{ date('Y-m-d', strtotime('-100 years')) }}" max="{{ date('Y-m-d', strtotime('-10 years')) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-custom" for="age_input">Age</label>
                        <input type="number" id="age_input" name="age" class="form-control-custom bg-light" value="{{ old('age') }}" readonly tabindex="-1" aria-readonly="true">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-custom" for="year_level">Year Level</label>
                        <select id="year_level" name="year_level" class="form-select-custom" required>
                            <option value="">Select</option>
                            @foreach(['1st Year','2nd Year','3rd Year','4th Year'] as $y)
                            <option value="{{ $y }}" {{ old('year_level') == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-section-heading mt-4"><span><i class="bi bi-mortarboard"></i></span> School details</div>
                <div class="row g-2 mb-4">
                    <div class="col-md-6">
                        <label class="form-label-custom" for="department">Course / Department</label>
                        <select id="department" name="department" class="form-select-custom" required>
                            <option value="">Select Course</option>
                            @foreach(['BEED', 'BSED', 'BSBA', 'BSHM', 'BSIT'] as $dept)
                            <option value="{{ $dept }}" {{ old('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-custom" for="student_id">Student ID</label>
                        <input type="text" id="student_id" name="student_id" class="form-control-custom" pattern="\d{4}-\d{4}" maxlength="9" title="Format: YYYY-XXXX" placeholder="YYYY-XXXX" value="{{ old('student_id') }}" autocomplete="off" required>
                    </div>
                </div>

                <div id="details-error" class="alert registration-error d-none mb-3" role="alert" aria-live="assertive"></div>

                <button type="button" id="btn-details-next" class="btn-primary-custom w-100 justify-content-center" style="padding:14px;">
                    <span>Continue to Account Security</span> <i class="bi bi-arrow-right-circle ms-1"></i>
                </button>
            </div>

            <!-- STEP 2: Account Security & Credentials -->
            <div id="step-2" class="{{ $hasAccountErrors ? '' : 'd-none' }}">
                <div class="form-section-heading"><span><i class="bi bi-shield-lock"></i></span> Account credentials</div>

                <div class="mb-3">
                    <label class="form-label-custom" for="email">Gmail Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light" style="border-color: #dbe3ef; border-radius: 12px 0 0 12px; color: #ea4335;"><i class="bi bi-google"></i></span>
                        <input type="email" id="email" name="email" class="form-control-custom" style="border-radius: 0 12px 12px 0;" placeholder="user@gmail.com" value="{{ old('email') }}" autocomplete="email" maxlength="255" required>
                    </div>
                    <div class="field-hint">Must be an active @gmail.com address for student access.</div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label-custom" for="register_password">Password</label>
                        <div style="position: relative;">
                            <input type="password" name="password" id="register_password" class="form-control-custom" minlength="8" autocomplete="new-password" aria-describedby="pw-panel" style="padding-right: 44px;" required>
                            <button type="button" onclick="togglePasswordVisibility('register_password', this)" style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); background: none; border: none; padding: 0; color: #64748b; font-size: 1.15rem; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 10;">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-custom" for="register_password_confirmation">Confirm Password</label>
                        <div style="position: relative;">
                            <input type="password" name="password_confirmation" id="register_password_confirmation" class="form-control-custom" minlength="8" autocomplete="new-password" aria-describedby="pw-match-hint" style="padding-right: 44px;" required>
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

                <div id="account-error" class="alert alert-danger d-none mb-3" role="alert" style="border-radius:var(--radius-sm);font-size:.85rem;">
                    <i class="bi bi-exclamation-triangle-fill"></i> <span id="account-error-text"></span>
                </div>

                @include('partials.captcha')

                <div class="d-flex gap-2">
                    <button type="button" id="btn-back-step" class="btn-secondary-custom" style="padding:14px; width:110px; display:flex; align-items:center; justify-content:center; gap:6px; font-weight:600; border-radius:var(--radius-sm); border:1.5px solid #cbd5e1; background:#fff; color:#475569;">
                        <i class="bi bi-arrow-left-circle"></i> Back
                    </button>
                    <button type="submit" id="btn-complete-registration" class="btn-primary-custom flex-grow-1 justify-content-center" style="padding:14px;">
                        <i class="bi bi-person-plus"></i> Complete Registration
                    </button>
                </div>
            </div>
        </form>

        <div class="registration-signin">
            Already registered? <a href="{{ route('login.student') }}">Sign in to your account <i class="bi bi-arrow-right"></i></a>
        </div>
        </section>
    </main>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const btnDetailsNext = document.getElementById('btn-details-next');
        const btnBackStep = document.getElementById('btn-back-step');
        const btnComplete = document.getElementById('btn-complete-registration');

        const stepDetails = document.getElementById('step-details');
        const step2 = document.getElementById('step-2');

        const detailsError = document.getElementById('details-error');
        const accountError = document.getElementById('account-error');
        const accountErrorText = document.getElementById('account-error-text');
        const progressSteps = document.querySelectorAll('.registration-step');

        const registerForm = document.getElementById('registerForm');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('register_password');
        const confirmInput = document.getElementById('register_password_confirmation');
        const pwMeterBar = document.getElementById('pw-meter-bar');
        const pwMeter = pwMeterBar ? pwMeterBar.parentElement : null;
        const pwStrengthText = document.getElementById('pw-strength-text');
        const pwMatchHint = document.getElementById('pw-match-hint');
        const pwReqNodes = document.querySelectorAll('#step-2 .pw-req');

        const detailsFields = stepDetails.querySelectorAll('input:not([readonly]), select');

        function setRegistrationStage(stage) {
            progressSteps.forEach((step) => {
                const number = Number(step.dataset.stage);
                step.classList.toggle('is-active', number === stage);
                step.classList.toggle('is-complete', number < stage);
                const marker = step.querySelector('.registration-step-number');
                marker.innerHTML = number < stage ? '<i class="bi bi-check-lg"></i>' : String(number);
            });
        }

        function clearFieldState(field) {
            field.setCustomValidity('');
            field.classList.remove('is-invalid-field');
        }

        function clearStepError(container) {
            container.classList.add('d-none');
            container.textContent = '';
        }

        function showStepError(container, message, field = null) {
            container.replaceChildren();

            const icon = document.createElement('i');
            icon.className = 'bi bi-exclamation-octagon-fill';

            const copy = document.createElement('div');
            copy.className = 'registration-error-copy';
            copy.textContent = message;

            container.append(icon, copy);
            container.classList.remove('d-none');
            if (field) {
                field.classList.add('is-invalid-field');
                field.focus();
            }
        }

        function fieldValidationError(field) {
            const value = field.value.trim();
            const label = field.closest('div')?.querySelector('label')?.textContent?.replace(/\s*\(optional\)\s*/, '').trim() || 'This field';

            if (field.required && !value) return `Please enter your ${label.toLowerCase()}.`;

            if (['first_name', 'middle_name', 'last_name'].includes(field.name) && value) {
                if (value.length < 2 && field.name !== 'middle_name') return `${label} must contain at least 2 characters.`;
                if (!/^[\p{L}][\p{L}\s.'-]*$/u.test(value)) return `${label} may contain letters, spaces, periods, apostrophes, and hyphens only.`;
            }

            if (field.name === 'dob' && value) {
                const [year, month, day] = value.split('-').map(Number);
                const dob = new Date(year, month - 1, day);
                const today = new Date();
                let age = today.getFullYear() - dob.getFullYear();
                if (today.getMonth() < dob.getMonth() || (today.getMonth() === dob.getMonth() && today.getDate() < dob.getDate())) age--;
                if (dob.getFullYear() !== year || dob.getMonth() !== month - 1 || dob.getDate() !== day || age < 10 || age > 100) {
                    return 'Enter a valid date of birth. Registrants must be between 10 and 100 years old.';
                }
            }

            if (field.name === 'student_id' && value && !/^\d{4}-\d{4}$/.test(value)) {
                return 'Student ID must use the format YYYY-XXXX (for example, 2024-0001).';
            }

            if (field.name === 'email' && value) {
                if (!field.validity.valid) return 'Enter a valid Gmail address.';
                if (!value.toLowerCase().endsWith('@gmail.com')) return 'Please use a valid @gmail.com address.';
            }

            return null;
        }

        function validateFields(fields, errorContainer) {
            clearStepError(errorContainer);
            for (const field of fields) {
                if (field.readOnly) continue;
                clearFieldState(field);
                const message = fieldValidationError(field);
                if (message) {
                    field.setCustomValidity(message);
                    showStepError(errorContainer, message, field);
                    return false;
                }
            }
            return true;
        }

        detailsFields.forEach((field) => {
            field.addEventListener(field.tagName === 'SELECT' ? 'change' : 'input', () => {
                clearFieldState(field);
                clearStepError(detailsError);
            });
            if (field.tagName === 'INPUT') {
                field.addEventListener('keydown', (e) => {
                    if (e.key !== 'Enter') return;
                    e.preventDefault();
                    btnDetailsNext.click();
                });
            }
        });

        // Continue to Step 2
        btnDetailsNext.addEventListener('click', (e) => {
            if (e) e.preventDefault();
            if (!validateFields(detailsFields, detailsError)) return;

            stepDetails.classList.add('d-none');
            step2.classList.remove('d-none');
            setRegistrationStage(2);
            emailInput.focus();
        });

        // Back to Step 1
        btnBackStep.addEventListener('click', () => {
            step2.classList.add('d-none');
            stepDetails.classList.remove('d-none');
            setRegistrationStage(1);
            document.getElementById('first_name').focus();
        });

        // ── Password Validation & Strength ──────────────────────────────────
        const STRENGTH_LEVELS = [
            { label: 'Very weak', color: '#ef4444', width: 15 },
            { label: 'Weak',      color: '#ef4444', width: 30 },
            { label: 'Fair',      color: '#f59e0b', width: 50 },
            { label: 'Good',      color: '#3b82f6', width: 72 },
            { label: 'Strong',    color: '#10b981', width: 88 },
            { label: 'Very strong', color: '#10b981', width: 100 }
        ];

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
            if (pw.length < 8 || !/[a-zA-Z]/.test(pw) || !/\d/.test(pw)) score = Math.min(score, 1);
            return score;
        }

        function evaluatePasswordSection() {
            if (!passwordInput) return false;

            const pw = passwordInput.value;
            const confirm = confirmInput.value;
            const checks = passwordChecks();

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

            passwordInput.classList.toggle('is-valid-field', checks.length && checks.letter && checks.number);
            passwordInput.classList.toggle('is-invalid-field', pw.length > 0 && !(checks.length && checks.letter && checks.number));

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

            const valid = checks.length && checks.letter && checks.number && checks.match;
            if (btnComplete) btnComplete.disabled = !valid;
            if (valid) hideAccountError();

            return valid;
        }

        function showAccountError(message) {
            accountErrorText.textContent = message;
            accountError.classList.remove('d-none');
        }

        function hideAccountError() {
            accountError.classList.add('d-none');
            accountErrorText.textContent = '';
        }

        if (passwordInput && confirmInput) {
            ['input', 'blur', 'paste'].forEach((evt) => {
                passwordInput.addEventListener(evt, () => setTimeout(evaluatePasswordSection, 0));
                confirmInput.addEventListener(evt, () => setTimeout(evaluatePasswordSection, 0));
            });
            evaluatePasswordSection();
        }

        emailInput.addEventListener('input', () => {
            clearFieldState(emailInput);
            hideAccountError();
        });

        // ── Form Submission ─────────────────────────────────────────────────
        registerForm.addEventListener('submit', (e) => {
            if (step2.classList.contains('d-none')) {
                e.preventDefault();
                btnDetailsNext.click();
                return false;
            }

            const emailVal = emailInput.value.trim().toLowerCase();
            if (!emailVal || !emailVal.endsWith('@gmail.com')) {
                e.preventDefault();
                showAccountError('Please provide a valid @gmail.com address.');
                emailInput.focus();
                return false;
            }

            if (!evaluatePasswordSection()) {
                e.preventDefault();
                const checks = passwordChecks();
                if (!checks.length || !checks.letter || !checks.number) {
                    showAccountError('Your password must be at least 8 characters long and include both a letter and a number.');
                    passwordInput.focus();
                } else {
                    showAccountError('The passwords you entered do not match. Please re-enter them.');
                    confirmInput.focus();
                }
                return false;
            }

            const verifiedTokenInput = document.getElementById('captcha_verified_token');
            if (verifiedTokenInput && !verifiedTokenInput.value) {
                e.preventDefault();
                showAccountError('Please complete the "I am not a robot" security check before continuing.');
                return false;
            }

            hideAccountError();
        });

        // If returned with account/credentials errors from server, set stage to 2
        @if($hasAccountErrors)
            setRegistrationStage(2);
        @endif

        // Date of Birth - Auto Age Calculator
        const dobInput = document.getElementById('dob_input');
        const ageInput = document.getElementById('age_input');
        if (dobInput && ageInput) {
            const updateAge = () => {
                if (!dobInput.value) {
                    ageInput.value = '';
                    return;
                }

                const [year, month, day] = dobInput.value.split('-').map(Number);
                const dob = new Date(year, month - 1, day);
                const today = new Date();
                let age = today.getFullYear() - dob.getFullYear();
                if (today.getMonth() < dob.getMonth() || (today.getMonth() === dob.getMonth() && today.getDate() < dob.getDate())) age--;
                ageInput.value = age >= 0 ? age : '';
            };

            dobInput.addEventListener('change', updateAge);
            updateAge();
        }
    });
</script>
<script src="{{ asset('assets/js/main.js') }}?v={{ @filemtime(public_path('assets/js/main.js')) ?: 1 }}"></script>
@include('partials.pwa-installer', ['floating' => true])
</body>
</html>
