@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-admin') @endsection

@section('content')
<style>
    .settings-card {
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        box-shadow: 0 1px 3px 0 rgba(15, 23, 42, 0.04), 0 1px 2px -1px rgba(15, 23, 42, 0.04);
        transition: box-shadow 0.2s ease, transform 0.2s ease;
        overflow: hidden;
    }
    .settings-card:hover {
        box-shadow: 0 4px 12px -2px rgba(15, 23, 42, 0.08), 0 2px 6px -1px rgba(15, 23, 42, 0.04);
    }
    .card-header-accent {
        padding: 1rem 1.25rem;
        background: #ffffff;
        border-bottom: 1px solid #f1f5f9;
    }
    .section-label {
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #64748b;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .section-label::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #e2e8f0;
    }
    .metric-pill {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.75rem 1rem;
        transition: all 0.15s ease;
    }
    .metric-pill:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }
    .icon-box {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }
    .pulse-dot-green {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #10b981;
        display: inline-block;
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        animation: pulseGreen 2s infinite;
    }
    @keyframes pulseGreen {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }
    .pulse-dot-red {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #ef4444;
        display: inline-block;
        box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
        animation: pulseRed 2s infinite;
    }
    @keyframes pulseRed {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
    }
</style>

<!-- Page Header -->
<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 style="font-weight: 800; font-size: 1.6rem; color: #0f172a;" class="mb-1">
            <i class="bi bi-gear-wide-connected text-primary me-2"></i>System Settings & Security
        </h1>
        <p class="text-muted mb-0" style="font-size: 0.88rem;">
            Configure academic school years, security authorization, maintenance controls, and database backups
        </p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-light text-secondary border px-3 py-2" style="font-size: 0.8rem; font-weight: 600;">
            <i class="bi bi-calendar-check text-primary me-1"></i> Term: {{ \App\Helpers\SscHelper::getActiveSchoolYear() }}
        </span>
        @if($maintenanceData['active'])
            <span class="badge bg-danger text-white px-3 py-2 shadow-sm" style="font-size: 0.8rem; font-weight: 700;">
                <span class="pulse-dot-red me-1"></span> Maintenance Active
            </span>
        @else
            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2" style="font-size: 0.8rem; font-weight: 600;">
                <span class="pulse-dot-green me-1"></span> System Operational
            </span>
        @endif
    </div>
</div>

<!-- ========================================================================= -->
<!-- SECTION 1: SECURITY & OPERATIONS CONTROLS                                  -->
<!-- ========================================================================= -->
<div class="section-label">
    <i class="bi bi-shield-lock-fill text-warning"></i> Security &amp; Operations Suite
</div>

<div class="row g-4 mb-4">
    <!-- Card 1: System Maintenance Mode -->
    <div class="col-lg-6">
        <div class="settings-card h-100 d-flex flex-column" style="border-top: 4px solid {{ $maintenanceData['active'] ? '#dc2626' : '#f59e0b' }};">
            <div class="card-header-accent d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <div class="icon-box" style="background: {{ $maintenanceData['active'] ? '#fef2f2' : '#fffbeb' }};">
                        <i class="bi bi-tools {{ $maintenanceData['active'] ? 'text-danger' : 'text-warning' }}"></i>
                    </div>
                    <div>
                        <h5 class="card-title mb-0" style="font-size: 1.02rem; font-weight: 700; color: #0f172a;">System Maintenance Mode</h5>
                        <div class="text-muted" style="font-size: 0.74rem;">Access restriction &amp; lockdown controls</div>
                    </div>
                </div>
                @if($maintenanceData['active'])
                    <span class="badge bg-danger px-3 py-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                        <i class="bi bi-cone-striped me-1"></i> ACTIVE
                    </span>
                @else
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-1" style="font-size: 0.72rem; font-weight: 600;">
                        <i class="bi bi-check-circle-fill me-1"></i> OPERATIONAL
                    </span>
                @endif
            </div>
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                @if($maintenanceData['active'])
                    <div>
                        <div class="alert alert-danger mb-3 p-3" style="border-radius: 10px; border-left: 4px solid #dc2626; background: #fff5f5;">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-exclamation-octagon-fill fs-5 text-danger flex-shrink-0 mt-1"></i>
                                <div>
                                    <strong style="font-size: 0.88rem; color: #991b1b;">Maintenance Mode is currently ACTIVE.</strong>
                                    <p class="mb-1 text-muted" style="font-size: 0.82rem; line-height: 1.45;">
                                        Students, officers, deans, and public visitors cannot access their portals and see the scheduled maintenance screen. Administrators retain full system access.
                                    </p>
                                    <div class="mt-2 pt-2 border-top border-danger border-opacity-25" style="font-size: 0.76rem; color: #7f1d1d;">
                                        <span><strong>Activated:</strong> {{ $maintenanceData['enabled_at'] ?? 'Recently' }} by {{ $maintenanceData['enabled_by_name'] ?? 'Admin' }}</span>
                                        <br>
                                        <span><strong>Notice:</strong> <em>"{{ $maintenanceData['message'] }}"</em></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-2 border-top">
                        <span class="text-muted" style="font-size: 0.78rem;">
                            <i class="bi bi-shield-check text-primary me-1"></i> Requires 6-digit OTP code to deactivate
                        </span>
                        <button type="button" id="btnDeactivateMaintenance" onclick="startMaintenanceOtpFlow('disable')" class="btn btn-sm btn-success fw-bold px-3 py-2" style="font-size: 0.82rem; border-radius: 8px;">
                            <i class="bi bi-check-circle-fill me-1"></i> Deactivate Maintenance Mode
                        </button>
                    </div>
                @else
                    <div>
                        <p class="text-muted mb-3" style="font-size: 0.82rem; line-height: 1.55;">
                            Activate maintenance mode during updates or database migrations. Non-admin users will be redirected to the scheduled maintenance screen while administrators retain full access.
                        </p>

                        <div class="mb-3">
                            <label for="maintenanceCustomMessage" class="form-label fw-bold text-dark mb-1" style="font-size: 0.8rem;">
                                Custom Public Announcement Notice (Optional)
                            </label>
                            <input type="text" id="maintenanceCustomMessage" class="form-control form-control-sm" 
                                   placeholder="e.g. Scheduled server maintenance in progress. Expected back in 1 hour." 
                                   value="The system is currently undergoing scheduled maintenance. Please check back shortly."
                                   style="font-size: 0.82rem; border-radius: 8px;">
                        </div>
                    </div>

                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-2 border-top">
                        <span class="text-muted" style="font-size: 0.78rem;">
                            <i class="bi bi-shield-check text-primary me-1"></i> Protected by 2FA Email OTP Verification
                        </span>
                        <button type="button" id="btnActivateMaintenance" onclick="startMaintenanceOtpFlow('enable')" class="btn btn-sm btn-outline-danger fw-bold px-3 py-2" style="font-size: 0.82rem; border-radius: 8px;">
                            <i class="bi bi-power me-1"></i> Activate Maintenance Mode
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Card 2: Database Backup & Security -->
    <div class="col-lg-6">
        <div class="settings-card h-100 d-flex flex-column" style="border-top: 4px solid #f59e0b;">
            <div class="card-header-accent d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <div class="icon-box" style="background: #fffbeb;">
                        <i class="bi bi-database-fill-gear text-warning"></i>
                    </div>
                    <div>
                        <h5 class="card-title mb-0" style="font-size: 1.02rem; font-weight: 700; color: #0f172a;">Database Backup &amp; Security</h5>
                        <div class="text-muted" style="font-size: 0.74rem;">Full SQL snapshot &amp; table records</div>
                    </div>
                </div>
                <button type="button" id="btnExportSql" onclick="requestExportOtp()" class="btn btn-sm btn-warning text-dark fw-bold px-3" style="font-size: 0.78rem; border-radius: 8px;">
                    <i class="bi bi-shield-lock-fill me-1"></i> Export SQL Backup
                </button>
            </div>
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                    <p class="text-muted mb-3" style="font-size: 0.82rem; line-height: 1.5;">
                        Full database backups download raw SQL structure and data. Protected with 2FA email verification to ensure only authorized administrators can export system records.
                    </p>
                    <div class="row g-2 mb-2">
                        @php
                            $tableIcons = [
                                'users' => 'bi-people-fill text-primary',
                                'budgets' => 'bi-wallet2 text-success',
                                'proposals' => 'bi-file-earmark-text-fill text-info',
                                'expenses' => 'bi-cash-coin text-warning',
                                'announcements' => 'bi-megaphone-fill text-danger',
                                'feedback' => 'bi-chat-left-dots-fill text-primary',
                                'activity_logs' => 'bi-clock-history text-secondary',
                                'liquidations' => 'bi-receipt-cutoff text-indigo',
                            ];
                        @endphp
                        @foreach($dbStats as $table => $count)
                            <div class="col-6 col-sm-3">
                                <div class="metric-pill text-center">
                                    <div class="d-flex align-items-center justify-content-center gap-1 mb-1">
                                        <i class="bi {{ $tableIcons[$table] ?? 'bi-table text-muted' }}" style="font-size: 0.82rem;"></i>
                                        <span style="font-size: 0.7rem; color: #64748b; font-family: monospace;">{{ $table }}</span>
                                    </div>
                                    <div style="font-size: 1.05rem; font-weight: 800; color: #0f172a;">{{ number_format($count) }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="pt-2 border-top text-muted d-flex align-items-center justify-content-between" style="font-size: 0.78rem;">
                    <span><i class="bi bi-shield-check text-success me-1"></i> Tables encrypted &amp; verified</span>
                    <span>Database: <code>{{ DB::getDatabaseName() }}</code></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- SECTION 2: ACADEMIC & GOVERNANCE CONTROLS                                  -->
<!-- ========================================================================= -->
<div class="section-label">
    <i class="bi bi-mortarboard-fill text-primary"></i> Academic &amp; Election Governance
</div>

<div class="row g-4 mb-4">
    <!-- Card 3: School Year Management -->
    <div class="col-lg-7">
        <div class="settings-card h-100 d-flex flex-column" style="border-top: 4px solid var(--primary, #2563eb);">
            <div class="card-header-accent d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div class="icon-box" style="background: #eff6ff;">
                        <i class="bi bi-calendar-range-fill text-primary"></i>
                    </div>
                    <div>
                        <h5 class="card-title mb-0" style="font-size: 1.02rem; font-weight: 700; color: #0f172a;">School Year Management</h5>
                        <div class="text-muted" style="font-size: 0.74rem;">Academic terms, semesters, and active session</div>
                    </div>
                </div>
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1" style="font-size: 0.75rem;">
                    {{ count($schoolYears) }} Registered Terms
                </span>
            </div>
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                    <form method="POST" action="{{ route('admin.settings.sy.add') }}" class="row g-2 align-items-center mb-3">
                        @csrf
                        <div class="col-sm-5">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-plus-circle text-muted"></i></span>
                                <input type="text" name="sy_label" class="form-control border-start-0" placeholder="YYYY-YYYY (e.g. 2026-2027)" required pattern="\d{4}-\d{4}">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <select name="semester" class="form-select form-select-sm" required aria-label="Semester">
                                @foreach(\App\Models\SchoolYear::SEMESTERS as $value => $label)
                                    <option value="{{ $value }}" @selected(old('semester', \App\Models\SchoolYear::SEMESTER_FIRST) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-3">
                            <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold" style="border-radius: 6px;">
                                <i class="bi bi-plus me-1"></i> Add SY
                            </button>
                        </div>
                    </form>

                    <div class="table-responsive" style="max-height: 280px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>School Year</th>
                                    <th>Semester</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($schoolYears as $sy)
                                <tr>
                                    <td style="font-weight: 700; color: #0f172a;">{{ $sy->label }}</td>
                                    <td>{{ $sy->semester_label }}</td>
                                    <td>
                                        @if($sy->is_active)
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">
                                                <i class="bi bi-check-circle-fill me-1"></i> Active
                                            </span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('admin.settings.sy.activate', $sy) }}" class="d-inline-flex align-items-center gap-1">
                                            @csrf @method('PATCH')
                                            <select name="semester" class="form-select form-select-sm" style="width: 125px; font-size: 0.72rem;" aria-label="Semester for {{ $sy->label }}">
                                                @foreach(\App\Models\SchoolYear::SEMESTERS as $value => $label)
                                                    <option value="{{ $value }}" @selected($sy->semester === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            <button class="btn {{ $sy->is_active ? 'btn-outline-primary' : 'btn-outline-success' }} btn-sm me-1" style="font-size: 0.72rem; font-weight: 600;">
                                                {{ $sy->is_active ? 'Update Term' : 'Set Active' }}
                                            </button>
                                        </form>
                                        @if(!$sy->is_active)
                                            <form method="POST" action="{{ route('admin.settings.sy.delete', $sy) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete school year {{ $sy->label }}?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-outline-danger btn-sm" style="font-size: 0.72rem;"><i class="bi bi-trash"></i></button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 4: Candidacy & Filing Control -->
    <div class="col-lg-5">
        <div class="settings-card h-100 d-flex flex-column" style="border-top: 4px solid #10b981;">
            <div class="card-header-accent d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div class="icon-box" style="background: #ecfdf5;">
                        <i class="bi bi-person-badge-fill text-success"></i>
                    </div>
                    <div>
                        <h5 class="card-title mb-0" style="font-size: 1.02rem; font-weight: 700; color: #0f172a;">Candidacy &amp; Filing Control</h5>
                        <div class="text-muted" style="font-size: 0.74rem;">Officer filing status &amp; elections</div>
                    </div>
                </div>
            </div>
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                    @php
                        $activeSy = \App\Models\SchoolYear::where('is_active', 1)->first();
                    @endphp
                    @if($activeSy)
                        <div class="p-3 border rounded-3 bg-light mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span style="font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Active Term</span>
                                <span class="badge bg-primary px-2 py-1" style="font-size: 0.72rem;">{{ $activeSy->academic_term }}</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="mb-0" style="font-weight: 700; color: #0f172a; font-size: 0.95rem;">Officer Candidacy Filing</h6>
                                    <div style="font-size: 0.78rem;" class="text-muted mt-1">
                                        Current Status: 
                                        @if($activeSy->candidacy_open)
                                            <span class="badge bg-success px-2 py-1">OPEN</span>
                                        @else
                                            <span class="badge bg-danger px-2 py-1">CLOSED</span>
                                        @endif
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('admin.settings.candidacy.toggle') }}">
                                    @csrf
                                    <button type="submit" class="btn {{ $activeSy->candidacy_open ? 'btn-danger' : 'btn-success' }} btn-sm" style="font-weight: 700; font-size: 0.8rem; padding: 8px 16px; border-radius: 8px;">
                                        <i class="bi {{ $activeSy->candidacy_open ? 'bi-lock-fill' : 'bi-unlock-fill' }} me-1"></i>
                                        {{ $activeSy->candidacy_open ? 'Close Filing' : 'Open Filing' }}
                                    </button>
                                </form>
                            </div>
                        </div>
                        <p class="text-muted mb-0" style="font-size: 0.8rem; line-height: 1.5;">
                            <i class="bi bi-info-circle text-primary me-1"></i> Opening filing enables students to submit candidacy applications for representative roles. Changing status automatically broadcasts an announcement.
                        </p>
                    @else
                        <div class="alert alert-warning mb-0" style="font-size: 0.82rem; border-radius: 8px;">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Please set an active school year to enable candidacy filing controls.
                        </div>
                    @endif
                </div>

                <div class="pt-3 mt-3 border-top d-flex align-items-center justify-content-between text-muted" style="font-size: 0.78rem;">
                    <span><i class="bi bi-broadcast me-1 text-info"></i> Broadcast notification integrated</span>
                    <a href="{{ route('admin.candidacies') }}" class="text-primary text-decoration-none fw-semibold">View Candidacies &rarr;</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- SECTION 3: SESSIONS & DIAGNOSTICS                                         -->
<!-- ========================================================================= -->
<div class="section-label">
    <i class="bi bi-cpu-fill text-indigo" style="color: #4f46e5;"></i> Session Security &amp; Diagnostics
</div>

<div class="row g-4 mb-4">
    <!-- Card 5: Active Logged-in Devices -->
    <div class="col-lg-7">
        <div class="settings-card h-100 d-flex flex-column" style="border-top: 4px solid #6366f1;">
            <div class="card-header-accent d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <div class="icon-box" style="background: #e0e7ff;">
                        <i class="bi bi-devices" style="color: #4f46e5;"></i>
                    </div>
                    <div>
                        <h5 class="card-title mb-0" style="font-size: 1.02rem; font-weight: 700; color: #0f172a;">Active Logged-in Devices</h5>
                        <div class="text-muted" style="font-size: 0.74rem;">Authorized browser sessions and primary device lock</div>
                    </div>
                </div>
                @if(count($activeSessions) > 1)
                    <form method="POST" action="{{ route('admin.settings.logout_others') }}" onsubmit="return confirm('Are you sure you want to terminate all other active device sessions? Any other browser currently logged in will be instantly signed out.')">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger" style="font-size: 0.75rem; font-weight: 600; border-radius: 6px;">
                            <i class="bi bi-box-arrow-right me-1"></i> Log Out All Other Devices
                        </button>
                    </form>
                @endif
            </div>
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                    <p style="font-size: 0.82rem; color: #475569; line-height: 1.5; margin-bottom: 14px;">
                        Review active browsers signed into your admin account. You can revoke authorization for any individual device.
                    </p>

                    @if(!empty($activeSessions) && count($activeSessions) > 0)
                        <div class="list-group mb-3" style="max-height: 240px; overflow-y: auto;">
                            @foreach($activeSessions as $session)
                                <div class="list-group-item d-flex align-items-center justify-content-between p-2 mb-2" style="border-radius: 8px; border: 1px solid #e2e8f0; background-color: {{ $session->is_current ? '#f0fdf4' : '#ffffff' }};">
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width: 36px; height: 36px; border-radius: 8px; background: {{ $session->is_current ? '#dcfce7' : '#f1f5f9' }}; display: flex; align-items: center; justify-content: center;">
                                            <i class="bi {{ $session->device_info['icon'] }}" style="font-size: 1.1rem; color: {{ $session->is_current ? '#16a34a' : '#3b82f6' }};"></i>
                                        </div>
                                        <div>
                                            <div class="d-flex align-items-center gap-2">
                                                <strong style="font-size: 0.85rem; color: #0f172a;">{{ $session->device_info['label'] }}</strong>
                                                @if($session->is_current)
                                                    <span class="badge bg-success" style="font-size: 0.65rem; font-weight: 600;"><i class="bi bi-check-circle-fill me-1"></i> This Device</span>
                                                @endif
                                            </div>
                                            <div class="text-muted" style="font-size: 0.74rem;">
                                                IP: <code>{{ $session->ip_address }}</code> &bull; Active {{ $session->last_activity }}
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        @if(!$session->is_current)
                                            <form method="POST" action="{{ route('admin.settings.logout_device', $session->id) }}" onsubmit="return confirm('Are you sure you want to log out this device?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm" style="font-size: 0.72rem; font-weight: 600; padding: 3px 8px;">
                                                    <i class="bi bi-box-arrow-right me-1"></i> Log Out
                                                </button>
                                            </form>
                                        @else
                                            <span class="badge bg-light text-success border border-success border-opacity-25" style="font-size: 0.72rem;"><i class="bi bi-shield-check me-1"></i> Current</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-secondary text-center py-2 mb-3" style="font-size: 0.8rem;">
                            No active database sessions tracked.
                        </div>
                    @endif
                </div>

                <div class="border-top pt-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span style="font-size: 0.8rem; font-weight: 700; color: #334155;">Primary Device Lock</span>
                        @if(Auth::user()->admin_device_token)
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1" style="font-size: 0.7rem;">
                                <i class="bi bi-shield-lock-fill me-1"></i> Token Active
                            </span>
                        @else
                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2 py-1" style="font-size: 0.7rem;">
                                <i class="bi bi-shield-exclamation me-1"></i> Unlocked
                            </span>
                        @endif
                    </div>
                    @if(Auth::user()->admin_device_token)
                        <div class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-light border">
                            <span class="text-muted" style="font-size: 0.76rem;">
                                Device locked. Unknown devices require approval/OTP.
                            </span>
                            <form method="POST" action="{{ route('admin.settings.reset_device') }}" onsubmit="return confirm('Are you sure you want to reset your registered primary device token?')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-secondary" style="font-weight: 600; font-size: 0.74rem;">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Token
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-light border">
                            <span class="text-muted" style="font-size: 0.76rem;">
                                Lock this current browser as the primary authorized device.
                            </span>
                            <form method="POST" action="{{ route('admin.settings.register_current_device') }}">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-primary" style="font-weight: 600; font-size: 0.74rem;">
                                    <i class="bi bi-shield-plus me-1"></i> Lock This Device
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Card 6: System & Environment Information -->
    <div class="col-lg-5">
        <div class="settings-card h-100 d-flex flex-column" style="border-top: 4px solid #0ea5e9;">
            <div class="card-header-accent d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <div class="icon-box" style="background: #e0f2fe;">
                        <i class="bi bi-info-circle text-info"></i>
                    </div>
                    <div>
                        <h5 class="card-title mb-0" style="font-size: 1.02rem; font-weight: 700; color: #0f172a;">Environment Diagnostics</h5>
                        <div class="text-muted" style="font-size: 0.74rem;">Server platform, runtime &amp; framework specs</div>
                    </div>
                </div>
                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1" style="font-size: 0.72rem; text-transform: uppercase;">
                    {{ app()->environment() }}
                </span>
            </div>
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div class="row g-2">
                    <div class="col-6">
                        <div class="metric-pill">
                            <div style="font-size: 0.7rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Laravel</div>
                            <div style="font-size: 0.92rem; font-family: monospace; font-weight: 700; color: #0f172a;">v{{ app()->version() }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="metric-pill">
                            <div style="font-size: 0.7rem; color: #64748b; font-weight: 600; text-transform: uppercase;">PHP Runtime</div>
                            <div style="font-size: 0.92rem; font-family: monospace; font-weight: 700; color: #0f172a;">v{{ PHP_VERSION }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="metric-pill">
                            <div style="font-size: 0.7rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Server Clock</div>
                            <div style="font-size: 0.88rem; font-weight: 700; color: #0f172a;">{{ now()->format('h:i A') }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="metric-pill">
                            <div style="font-size: 0.7rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Session Driver</div>
                            <div style="font-size: 0.88rem; font-weight: 700; color: #16a34a; font-family: monospace;">{{ config('session.driver') }}</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="metric-pill">
                            <div style="font-size: 0.7rem; color: #64748b; font-weight: 600; text-transform: uppercase;">System Name</div>
                            <div style="font-size: 0.92rem; font-weight: 700; color: #0f172a;">SSC Transparency &amp; Budget System</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="metric-pill">
                            <div style="font-size: 0.7rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Timezone &amp; Cache</div>
                            <div style="font-size: 0.85rem; font-weight: 600; color: #475569;">
                                {{ config('app.timezone') }} &bull; Driver: <code>{{ config('cache.default') }}</code>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-top text-muted d-flex align-items-center justify-content-between" style="font-size: 0.76rem;">
                    <span><i class="bi bi-shield-check text-success me-1"></i> HTTPS &amp; Security Headers active</span>
                    <span>SSC v2.4</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODALS                                                                    -->
<!-- ========================================================================= -->

<!-- Modal: OTP Verification for Maintenance Mode Toggle -->
<div class="modal fade" id="maintenanceOtpModal" tabindex="-1" aria-labelledby="maintenanceOtpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
            <div class="modal-header text-white py-3" id="maintenanceModalHeader" style="background: #1e3a8a;">
                <h5 class="modal-title fw-bold" id="maintenanceOtpModalLabel" style="font-size: 1.05rem;">
                    <i class="bi bi-shield-lock me-2"></i>Maintenance Mode Authorization
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('admin.settings.maintenance.toggle') }}">
                @csrf
                <input type="hidden" name="action" id="maintenanceTargetAction" value="enable">
                <input type="hidden" name="message" id="maintenanceModalMessage" value="">

                <div class="modal-body p-4 text-center">
                    <div class="mb-3">
                        <div id="maintenanceModalIconBg" style="width: 58px; height: 58px; border-radius: 50%; background: #eff6ff; display: inline-flex; align-items: center; justify-content: center;" class="mb-2">
                            <i id="maintenanceModalIcon" class="bi bi-cone-striped text-primary" style="font-size: 1.7rem;"></i>
                        </div>
                        <h6 style="font-weight: 700; color: #0f172a;" id="maintenanceModalActionTitle">Security Authorization Required</h6>
                        <p class="text-muted mb-0" style="font-size: 0.85rem;" id="otpMaintenanceStatusMsg">
                            A secure 6-digit verification code has been sent to <strong>{{ Auth::user()->email }}</strong>.
                        </p>
                    </div>

                    <div class="mb-3">
                        <label for="otpMaintenanceCodeInput" class="form-label fw-bold text-dark" style="font-size: 0.85rem;">Enter 6-Digit OTP Code</label>
                        <input type="text" name="otp" id="otpMaintenanceCodeInput" class="form-control form-control-lg text-center fw-bold" 
                               placeholder="123456" maxlength="6" pattern="\d{6}" required autofocus 
                               style="letter-spacing: 8px; font-size: 1.6rem; color: #1e3a8a; background: #f8fafc; border-radius: 10px;">
                    </div>

                    <div style="font-size: 0.78rem;" class="text-muted mb-1">
                        <i class="bi bi-clock-history me-1"></i> Code is valid for 3 minutes.
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-4 d-flex justify-content-between">
                    <button type="button" onclick="resendMaintenanceOtp()" class="btn btn-link btn-sm text-decoration-none text-muted" style="font-weight: 600;">
                        <i class="bi bi-arrow-clockwise me-1"></i> Resend Code
                    </button>
                    <div>
                        <button type="button" class="btn btn-secondary btn-sm me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="btnConfirmMaintenance" class="btn btn-primary btn-sm fw-bold px-3" style="border-radius: 8px;">
                            <i class="bi bi-check2-circle me-1"></i> Confirm &amp; Save
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: OTP Verification for SQL Backup Download -->
<div class="modal fade" id="exportOtpModal" tabindex="-1" aria-labelledby="exportOtpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold" id="exportOtpModalLabel" style="font-size: 1.05rem;">
                    <i class="bi bi-shield-lock me-2"></i>Database Export Verification
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('admin.settings.export') }}">
                @csrf
                <div class="modal-body p-4 text-center">
                    <div class="mb-3">
                        <div style="width: 58px; height: 58px; border-radius: 50%; background: #eff6ff; display: inline-flex; align-items: center; justify-content: center;" class="mb-2">
                            <i class="bi bi-envelope-check-fill text-primary" style="font-size: 1.7rem;"></i>
                        </div>
                        <h6 style="font-weight: 700; color: #0f172a;">Verification Code Required</h6>
                        <p class="text-muted mb-0" style="font-size: 0.85rem;" id="otpStatusMsg">
                            A secure 6-digit verification code has been sent to <strong>{{ Auth::user()->email }}</strong>.
                        </p>
                    </div>

                    <div class="mb-3">
                        <label for="otpCodeInput" class="form-label fw-bold text-dark" style="font-size: 0.85rem;">Enter 6-Digit OTP Code</label>
                        <input type="text" name="otp" id="otpCodeInput" class="form-control form-control-lg text-center fw-bold" 
                               placeholder="123456" maxlength="6" pattern="\d{6}" required autofocus 
                               style="letter-spacing: 8px; font-size: 1.6rem; color: #1e3a8a; background: #f8fafc; border-radius: 10px;">
                    </div>

                    <div style="font-size: 0.78rem;" class="text-muted mb-1">
                        <i class="bi bi-clock-history me-1"></i> Code is valid for 3 minutes.
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-4 d-flex justify-content-between">
                    <button type="button" onclick="requestExportOtp()" class="btn btn-link btn-sm text-decoration-none text-muted" style="font-weight: 600;">
                        <i class="bi bi-arrow-clockwise me-1"></i> Resend Code
                    </button>
                    <div>
                        <button type="button" class="btn btn-secondary btn-sm me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning btn-sm text-dark fw-bold px-3" style="border-radius: 8px;">
                            <i class="bi bi-download me-1"></i> Confirm &amp; Download
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentMaintenanceAction = 'enable';

function startMaintenanceOtpFlow(action) {
    currentMaintenanceAction = action;
    const isEnable = (action === 'enable');
    const btn = document.getElementById(isEnable ? 'btnActivateMaintenance' : 'btnDeactivateMaintenance');
    const originalHtml = btn ? btn.innerHTML : '';

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sending Code...';
    }

    const messageInput = document.getElementById('maintenanceCustomMessage');
    const customMessage = messageInput ? messageInput.value : '';

    fetch("{{ route('admin.settings.maintenance.request_otp') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({
            target_action: action
        })
    })
    .then(response => response.json())
    .then(data => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }

        if (data.success) {
            document.getElementById('maintenanceTargetAction').value = action;
            document.getElementById('maintenanceModalMessage').value = customMessage;

            const modalHeader = document.getElementById('maintenanceModalHeader');
            const confirmBtn = document.getElementById('btnConfirmMaintenance');
            const title = document.getElementById('maintenanceModalActionTitle');
            const statusMsg = document.getElementById('otpMaintenanceStatusMsg');

            if (statusMsg) {
                statusMsg.innerHTML = data.message;
            }

            if (isEnable) {
                modalHeader.style.background = '#dc2626';
                confirmBtn.className = 'btn btn-danger btn-sm fw-bold px-3';
                confirmBtn.innerHTML = '<i class="bi bi-power me-1"></i> Confirm & Activate';
                title.innerText = 'Authorize Activation of Maintenance Mode';
            } else {
                modalHeader.style.background = '#16a34a';
                confirmBtn.className = 'btn btn-success btn-sm fw-bold px-3';
                confirmBtn.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Confirm & Deactivate';
                title.innerText = 'Authorize Deactivation of Maintenance Mode';
            }

            const modal = new bootstrap.Modal(document.getElementById('maintenanceOtpModal'));
            modal.show();
        } else {
            alert(data.message || 'Failed to send OTP code. Please try again.');
        }
    })
    .catch(error => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
        alert('Error connecting to server. Please try again.');
    });
}

function resendMaintenanceOtp() {
    startMaintenanceOtpFlow(currentMaintenanceAction);
}

function requestExportOtp() {
    const btn = document.getElementById('btnExportSql');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sending Code...';
    }

    fetch("{{ route('admin.settings.export.request_otp') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        }
    })
    .then(response => response.json())
    .then(data => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-shield-lock-fill me-1"></i> Export SQL Backup';
        }

        if (data.success) {
            const statusMsg = document.getElementById('otpStatusMsg');
            if (statusMsg) {
                statusMsg.innerHTML = data.message;
            }
            const modal = new bootstrap.Modal(document.getElementById('exportOtpModal'));
            modal.show();
        } else {
            alert(data.message || 'Failed to send OTP code. Please try again.');
        }
    })
    .catch(error => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-shield-lock-fill me-1"></i> Export SQL Backup';
        }
        alert('Error connecting to server. Please try again.');
    });
}
</script>
@endsection
