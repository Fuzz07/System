@php
    $pageTitle = 'Account Details';
    $pageSubtitle = 'Edit your student profile';
@endphp
@extends('layouts.mobile-student', ['showBack' => true, 'backUrl' => route('mobile.student.proposals')])

@section('content')
<style>
.profile-hero{margin:14px 0;padding:20px;border-radius:20px;color:#fff;background:linear-gradient(145deg,#c2410c,#f97316);display:flex;align-items:center;gap:14px;box-shadow:0 14px 30px rgba(194,65,12,.2)}
.profile-avatar{width:58px;height:58px;border-radius:50%;display:grid;place-items:center;flex:0 0 auto;background:rgba(255,255,255,.2);border:1px solid rgba(255,255,255,.28);font-size:1.35rem;font-weight:800}
.profile-card{margin-bottom:14px;padding:18px;border-radius:18px;background:#fff;border:1px solid var(--slate-200)}
.profile-section-title{display:flex;align-items:center;gap:8px;margin-bottom:16px;color:var(--slate-900);font-weight:800}
.profile-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px 10px}.profile-grid .wide{grid-column:1/-1}
.profile-input{width:100%;min-height:46px;padding:11px 12px;border:1px solid var(--slate-300);border-radius:12px;background:#fff;color:var(--slate-900);font:inherit;font-size:.86rem}.profile-input:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px rgba(227,79,38,.12)}
.profile-help{font-size:.69rem;color:var(--slate-500);line-height:1.45;margin-top:5px}
@media(max-width:370px){.profile-grid{grid-template-columns:1fr}.profile-grid .wide{grid-column:auto}}
</style>

<section class="profile-hero">
    <div class="profile-avatar">{{ $student->avatar }}</div>
    <div style="min-width:0">
        <div style="font-size:1.05rem;font-weight:800;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $student->fullname }}</div>
        <div style="font-size:.76rem;opacity:.8;margin-top:3px">{{ $student->student_id ?: 'Student account' }}</div>
    </div>
</section>

<form method="POST" action="{{ route('mobile.student.profile.update') }}" autocomplete="on">
    @csrf
    @method('PUT')

    <section class="profile-card">
        <div class="profile-section-title"><i class="bi bi-person-lines-fill text-primary"></i> Personal information</div>
        <div class="profile-grid">
            <div class="m-field"><label for="first_name">First name</label><input id="first_name" name="first_name" class="profile-input" value="{{ old('first_name', $student->first_name) }}" minlength="2" maxlength="100" autocomplete="given-name" required></div>
            <div class="m-field"><label for="last_name">Last name</label><input id="last_name" name="last_name" class="profile-input" value="{{ old('last_name', $student->last_name) }}" minlength="2" maxlength="100" autocomplete="family-name" required></div>
            <div class="m-field wide"><label for="middle_name">Middle name (optional)</label><input id="middle_name" name="middle_name" class="profile-input" value="{{ old('middle_name', $student->middle_name) }}" maxlength="100" autocomplete="additional-name"></div>
            <div class="m-field"><label for="age">Age</label><input id="age" type="number" name="age" class="profile-input" value="{{ old('age', $student->age) }}" min="10" max="100" inputmode="numeric" required></div>
            <div class="m-field"><label for="year_level">Year level</label><select id="year_level" name="year_level" class="profile-input" required>@foreach(['1st Year','2nd Year','3rd Year','4th Year'] as $level)<option value="{{ $level }}" @selected(old('year_level', $student->year_level) === $level)>{{ $level }}</option>@endforeach</select></div>
            <div class="m-field wide"><label for="department">Course / department</label><select id="department" name="department" class="profile-input" required>@foreach(['BEED','BSED','BSBA','BSHM','BSIT'] as $department)<option value="{{ $department }}" @selected(old('department', $student->department) === $department)>{{ $department }}</option>@endforeach</select></div>
            <div class="m-field wide"><label for="student_id">Student ID</label><input id="student_id" name="student_id" class="profile-input" value="{{ old('student_id', $student->student_id) }}" pattern="\d{4}-\d{4}" maxlength="9" placeholder="YYYY-XXXX" required></div>
            <div class="m-field wide"><label for="email">Gmail address</label><input id="email" type="email" name="email" class="profile-input" value="{{ old('email', $student->email) }}" autocomplete="email" required><div class="profile-help">Enter your current password below if you change this email.</div></div>
        </div>
    </section>

    <section class="profile-card">
        <div class="profile-section-title"><i class="bi bi-shield-lock-fill text-primary"></i> Account security</div>
        <div class="m-field" style="margin-bottom:14px"><label for="current_password">Current password</label><input id="current_password" type="password" name="current_password" class="profile-input" autocomplete="current-password"></div>
        <div class="profile-grid">
            <div class="m-field"><label for="password">New password</label><input id="password" type="password" name="password" class="profile-input" minlength="8" autocomplete="new-password"></div>
            <div class="m-field"><label for="password_confirmation">Confirm password</label><input id="password_confirmation" type="password" name="password_confirmation" class="profile-input" minlength="8" autocomplete="new-password"></div>
        </div>
        <div class="profile-help" style="margin-top:10px">Leave the password fields blank to keep your current password.</div>
    </section>

    <button type="submit" class="m-btn m-btn-primary m-btn-block" style="min-height:50px;margin:4px 0 18px;font-weight:800"><i class="bi bi-check2-circle"></i> Save account details</button>
</form>
@endsection
