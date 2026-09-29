@php
    $pageTitle = 'Account Details';
    $pageSubtitle = 'Keep your student information up to date';
    $dashUrl = route('student.overview');
@endphp
@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-student') @endsection

@section('content')
<div class="page-header">
    <div>
        <h1>Account Details</h1>
        <p>Update your personal, academic, and sign-in information.</p>
    </div>
</div>

<form method="POST" action="{{ route('student.profile.update') }}" autocomplete="on">
    @csrf
    @method('PUT')

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header-custom">
                    <span class="card-title"><i class="bi bi-person-lines-fill me-2"></i>Personal information</span>
                </div>
                <div class="card-body-custom">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="first_name" class="form-label-custom">First name</label>
                            <input id="first_name" name="first_name" class="form-control-custom" value="{{ old('first_name', $student->first_name) }}" minlength="2" maxlength="100" autocomplete="given-name" required>
                        </div>
                        <div class="col-md-4">
                            <label for="middle_name" class="form-label-custom">Middle name <span class="text-muted fw-normal">(optional)</span></label>
                            <input id="middle_name" name="middle_name" class="form-control-custom" value="{{ old('middle_name', $student->middle_name) }}" maxlength="100" autocomplete="additional-name">
                        </div>
                        <div class="col-md-4">
                            <label for="last_name" class="form-label-custom">Last name</label>
                            <input id="last_name" name="last_name" class="form-control-custom" value="{{ old('last_name', $student->last_name) }}" minlength="2" maxlength="100" autocomplete="family-name" required>
                        </div>
                        <div class="col-md-4">
                            <label for="birthdate" class="form-label-custom">Date of birth</label>
                            <input id="birthdate" type="date" name="birthdate" class="form-control-custom" value="{{ old('birthdate', $student->birthdate?->toDateString()) }}" min="{{ \App\Models\User::earliestBirthdate() }}" max="{{ \App\Models\User::latestBirthdate() }}" data-birthdate-input data-age-target="age_display" autocomplete="bday" required>
                            <div class="form-text">You must be at least {{ \App\Models\User::MIN_AGE }} years old.</div>
                        </div>
                        <div class="col-md-2">
                            <label for="age_display" class="form-label-custom">Age</label>
                            <input id="age_display" type="text" class="form-control-custom bg-light" value="{{ $student->birthdate?->age }}" readonly tabindex="-1" aria-readonly="true">
                        </div>
                        <div class="col-md-3">
                            <label for="year_level" class="form-label-custom">Year level</label>
                            <select id="year_level" name="year_level" class="form-select-custom" required>
                                @foreach(['1st Year', '2nd Year', '3rd Year', '4th Year'] as $level)
                                    <option value="{{ $level }}" @selected(old('year_level', $student->year_level) === $level)>{{ $level }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="department" class="form-label-custom">Course / department</label>
                            <select id="department" name="department" class="form-select-custom" required>
                                @foreach(['BEED', 'BSED', 'BSBA', 'BSHM', 'BSIT'] as $department)
                                    <option value="{{ $department }}" @selected(old('department', $student->department) === $department)>{{ $department }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="student_id" class="form-label-custom">Student ID</label>
                            <input id="student_id" name="student_id" class="form-control-custom" value="{{ old('student_id', $student->student_id) }}" pattern="\d{4}-\d{4}" maxlength="9" placeholder="YYYY-XXXX" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label-custom">Gmail address</label>
                            <input id="email" type="email" name="email" class="form-control-custom" value="{{ old('email', $student->email) }}" maxlength="255" autocomplete="email" required>
                            <div class="form-text">Changing your email requires your current password.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header-custom">
                    <span class="card-title"><i class="bi bi-shield-lock-fill me-2"></i>Account security</span>
                </div>
                <div class="card-body-custom">
                    <p class="text-muted small mb-3">Leave the new-password fields blank to keep your current password.</p>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="current_password" class="form-label-custom">Current password</label>
                            <input id="current_password" type="password" name="current_password" class="form-control-custom" autocomplete="current-password">
                        </div>
                        <div class="col-md-4">
                            <label for="password" class="form-label-custom">New password</label>
                            <input id="password" type="password" name="password" class="form-control-custom" minlength="8" autocomplete="new-password">
                        </div>
                        <div class="col-md-4">
                            <label for="password_confirmation" class="form-label-custom">Confirm new password</label>
                            <input id="password_confirmation" type="password" name="password_confirmation" class="form-control-custom" minlength="8" autocomplete="new-password">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card position-sticky" style="top:88px;">
                <div class="card-body-custom text-center">
                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center text-white fw-bold" style="width:72px;height:72px;border-radius:50%;font-size:1.6rem;background:linear-gradient(135deg,var(--primary),var(--primary-dark));">
                        {{ $student->avatar }}
                    </div>
                    <h2 class="h5 fw-bold mb-1">{{ $student->fullname }}</h2>
                    <div class="text-muted small mb-4">{{ $student->student_id ?: 'Student account' }}</div>
                    <button type="submit" class="btn-primary-custom w-100 justify-content-center">
                        <i class="bi bi-check2-circle"></i> Save account details
                    </button>
                    <div class="small text-muted mt-3"><i class="bi bi-lock me-1"></i>Your password is never displayed.</div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
    @include('partials.birthdate-age-script')
@endpush
