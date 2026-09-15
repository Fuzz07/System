@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-admin') @endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 style="font-weight: 800; font-size: 1.6rem; color: #0f172a;" class="mb-1">
            <i class="bi bi-gear-wide-connected text-primary me-2"></i>System Settings & Security
        </h1>
        <p class="text-muted mb-0" style="font-size: 0.88rem;">Manage academic school years, security authorization, and system database backups</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2" style="font-size: 0.8rem; font-weight: 600;">
            <i class="bi bi-shield-check me-1"></i> System Status: Operational
        </span>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: School Year & Candidacy Control -->
    <div class="col-lg-6">
        <!-- School Year Management -->
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between border-bottom" style="border-top: 4px solid var(--primary, #2563eb);">
                <div class="d-flex align-items-center gap-2">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: #eff6ff; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-calendar-range-fill text-primary"></i>
                    </div>
                    <h5 class="card-title mb-0" style="font-size: 1.05rem; font-weight: 700; color: #0f172a;">School Year Management</h5>
                </div>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('admin.settings.sy.add') }}" class="d-flex flex-wrap gap-2 mb-4">
                    @csrf
                    <div class="input-group flex-grow-1" style="min-width: 220px;">
                        <span class="input-group-text bg-light border-end-0" style="font-size: 0.85rem;"><i class="bi bi-plus-circle text-muted"></i></span>
                        <input type="text" name="sy_label" class="form-control form-control-sm border-start-0" placeholder="YYYY-YYYY (e.g. 2026-2027)" required pattern="\d{4}-\d{4}">
                    </div>
                    <select name="semester" class="form-select form-select-sm" style="width: 155px;" required aria-label="Semester">
                        @foreach(\App\Models\SchoolYear::SEMESTERS as $value => $label)
                            <option value="{{ $value }}" @selected(old('semester', \App\Models\SchoolYear::SEMESTER_FIRST) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm px-3" style="font-weight: 600; min-width: 90px;"><i class="bi bi-plus me-1"></i> Add SY</button>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                        <thead class="table-light">
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
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i> Active</span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('admin.settings.sy.activate', $sy) }}" class="d-inline-flex align-items-center gap-1">
                                        @csrf @method('PATCH')
                                        <select name="semester" class="form-select form-select-sm" style="width: 132px; font-size: 0.72rem;" aria-label="Semester for {{ $sy->label }}">
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

        <!-- Candidacy & Filing Control -->
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between border-bottom" style="border-top: 4px solid #10b981;">
                <div class="d-flex align-items-center gap-2">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: #ecfdf5; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-person-badge-fill text-success"></i>
                    </div>
                    <h5 class="card-title mb-0" style="font-size: 1.05rem; font-weight: 700; color: #0f172a;">Candidacy & Filing Control</h5>
                </div>
            </div>
            <div class="card-body p-4">
                @php
                    $activeSy = \App\Models\SchoolYear::where('is_active', 1)->first();
                @endphp
                @if($activeSy)
                    <div class="d-flex align-items-center justify-content-between p-3 border rounded-3 bg-light mb-3">
                        <div>
                            <h6 class="mb-1" style="font-weight: 700; color: #0f172a;">SSC Officer Candidacy Filing</h6>
                            <div style="font-size: 0.82rem;" class="text-muted">
                                Active Academic Term: <strong>{{ $activeSy->academic_term }}</strong> &nbsp;•&nbsp;
                                Status: 
                                @if($activeSy->candidacy_open)
                                    <span class="badge bg-success px-2 py-1">OPEN</span>
                                @else
                                    <span class="badge bg-danger px-2 py-1">CLOSED</span>
                                @endif
                            </div>
                        </div>
                        <form method="POST" action="{{ route('admin.settings.candidacy.toggle') }}">
                            @csrf
                            <button type="submit" class="btn {{ $activeSy->candidacy_open ? 'btn-danger' : 'btn-success' }} btn-sm" style="font-weight: 700; font-size: 0.8rem; padding: 8px 16px;">
                                <i class="bi {{ $activeSy->candidacy_open ? 'bi-lock-fill' : 'bi-unlock-fill' }} me-1"></i>
                                {{ $activeSy->candidacy_open ? 'Close Filing' : 'Open Filing' }}
                            </button>
                        </form>
                    </div>
                    <p class="text-muted mb-0" style="font-size: 0.78rem; line-height: 1.4;">
                        <i class="bi bi-info-circle text-primary me-1"></i> Opening filing allows students to submit candidacy applications for department representatives. Toggling status automatically broadcasts a portal announcement.
                    </p>
                @else
                    <div class="alert alert-warning mb-0" style="font-size: 0.82rem;">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Please set an active school year above to enable candidacy filing controls.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Right Column: Database Backup & Active Devices -->
    <div class="col-lg-6">
        <!-- System Maintenance Mode Card -->
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom" style="border-top: 4px solid {{ $maintenanceData['active'] ? '#ef4444' : '#f59e0b' }};">
                <div class="d-flex align-items-center gap-2">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: {{ $maintenanceData['active'] ? '#fef2f2' : '#fffbeb' }}; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-tools {{ $maintenanceData['active'] ? 'text-danger' : 'text-warning' }}"></i>
                    </div>
                    <div>
                        <h5 class="card-title mb-0" style="font-size: 1.05rem; font-weight: 700; color: #0f172a;">System Maintenance Mode</h5>
                    </div>
                </div>
                @if($maintenanceData['active'])
                    <span class="badge bg-danger px-3 py-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                        <i class="bi bi-exclamation-octagon-fill me-1"></i> MAINTENANCE ACTIVE
                    </span>
                @else
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2" style="font-size: 0.75rem; font-weight: 600;">
                        <i class="bi bi-check-circle-fill me-1"></i> OPERATIONAL
                    </span>
                @endif
            </div>
            <div class="card-body p-4">
                @if($maintenanceData['active'])
                    <div class="alert alert-danger mb-3 p-3" style="border-radius: 8px; border-left: 4px solid #dc2626;">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-cone-striped fs-5 text-danger flex-shrink-0 mt-1"></i>
                            <div>
                                <strong style="font-size: 0.9rem;">Maintenance Mode is currently ACTIVE.</strong>
                                <p class="mb-1 text-muted" style="font-size: 0.82rem;">
                                    Public, student, officer, and dean portals are locked and displaying the scheduled maintenance screen. Administrators retain full access to manage the system.
                                </p>
                                <div class="mt-2 pt-2 border-top border-danger border-opacity-25" style="font-size: 0.78rem; color: #991b1b;">
                                    <span><strong>Enabled:</strong> {{ $maintenanceData['enabled_at'] ?? 'Recently' }} by {{ $maintenanceData['enabled_by_name'] ?? 'Admin' }}</span>
                                    <br>
                                    <span><strong>Broadcast Message:</strong> <em>"{{ $maintenanceData['message'] }}"</em></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted" style="font-size: 0.8rem;">
                            <i class="bi bi-shield-lock text-primary me-1"></i> Requires email OTP code to deactivate
                        </span>
                        <button type="button" id="btnDeactivateMaintenance" onclick="startMaintenanceOtpFlow('disable')" class="btn btn-sm btn-success fw-bold px-3 py-2" style="font-size: 0.82rem;">
                            <i class="bi bi-check-circle-fill me-1"></i> Deactivate Maintenance Mode
                        </button>
                    </div>
                @else
                    <p class="text-muted mb-3" style="font-size: 0.82rem; line-height: 1.5;">
                        When maintenance mode is activated, all public, student, and staff pages are blocked and display a scheduled maintenance screen. Only administrators can access the system.
                    </p>

                    <div class="mb-3">
                        <label for="maintenanceCustomMessage" class="form-label fw-bold text-dark mb-1" style="font-size: 0.8rem;">
                            Custom Maintenance Notice (Optional)
                        </label>
                        <input type="text" id="maintenanceCustomMessage" class="form-control form-control-sm" 
                               placeholder="e.g. Scheduled server maintenance in progress. Expected back in 1 hour." 
                               value="The system is currently undergoing scheduled maintenance. Please check back shortly.">
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-2">
                        <span class="text-muted" style="font-size: 0.8rem;">
                            <i class="bi bi-shield-lock text-primary me-1"></i> Requires email OTP code to activate
                        </span>
                        <button type="button" id="btnActivateMaintenance" onclick="startMaintenanceOtpFlow('enable')" class="btn btn-sm btn-outline-danger fw-bold px-3 py-2" style="font-size: 0.82rem;">
                            <i class="bi bi-power me-1"></i> Activate Maintenance Mode
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <!-- Database Overview & OTP Backup Export -->
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom" style="border-top: 4px solid #f59e0b;">
                <div class="d-flex align-items-center gap-2">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: #fffbeb; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-database-fill-gear text-warning"></i>
                    </div>
                    <h5 class="card-title mb-0" style="font-size: 1.05rem; font-weight: 700; color: #0f172a;">Database Backup & Security</h5>
                </div>
                <button type="button" id="btnExportSql" onclick="requestExportOtp()" class="btn btn-sm btn-warning text-dark fw-bold" style="font-size: 0.78rem; border-radius: 6px;">
                    <i class="bi bi-shield-lock-fill me-1"></i> Export SQL Backup
                </button>
            </div>
            <div class="card-body p-4">
                <p class="text-muted mb-3" style="font-size: 0.82rem; line-height: 1.5;">
                    Database backup downloads are protected with 2FA email verification. Clicking <strong>Export SQL Backup</strong> will send a 6-digit verification OTP code to your admin email address.
                </p>
                <div class="row g-2 mb-3">
                    @foreach($dbStats as $table => $count)
                        <div class="col-6 col-md-4">
                            <div class="p-2 border rounded-3 bg-light text-center">
                                <div style="font-size: 0.72rem; color: #64748b; font-family: monospace;">{{ $table }}</div>
                                <div style="font-size: 0.95rem; font-weight: 800; color: #0f172a;">{{ number_format($count) }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Active Logged-in Devices & Security -->
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom" style="border-top: 4px solid #6366f1;">
                <div class="d-flex align-items-center gap-2">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: #e0e7ff; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-devices text-indigo" style="color: #4f46e5;"></i>
                    </div>
                    <h5 class="card-title mb-0" style="font-size: 1.05rem; font-weight: 700; color: #0f172a;">Active Logged-in Devices</h5>
                </div>
                @if(count($activeSessions) > 1)
                    <form method="POST" action="{{ route('admin.settings.logout_others') }}" onsubmit="return confirm('Are you sure you want to terminate all other active device sessions? Any other browser currently logged in will be instantly signed out.')">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger" style="font-size: 0.75rem; font-weight: 600;">
                            <i class="bi bi-box-arrow-right me-1"></i> Log Out All Other Devices
                        </button>
                    </form>
                @endif
            </div>
            <div class="card-body p-4">
                <p style="font-size: 0.82rem; color: #475569; line-height: 1.5; margin-bottom: 16px;">
                    Review active devices and browsers signed into your admin account. You can selectively log out any individual session.
                </p>

                @if(!empty($activeSessions) && count($activeSessions) > 0)
                    <div class="list-group mb-3">
                        @foreach($activeSessions as $session)
                            <div class="list-group-item d-flex align-items-center justify-content-between p-3 mb-2" style="border-radius: 8px; border: 1px solid #e2e8f0; background-color: {{ $session->is_current ? '#f0fdf4' : '#ffffff' }};">
                                <div class="d-flex align-items-center gap-3">
                                    <div style="width: 42px; height: 42px; border-radius: 8px; background: {{ $session->is_current ? '#dcfce7' : '#f1f5f9' }}; display: flex; align-items: center; justify-content: center;">
                                        <i class="bi {{ $session->device_info['icon'] }}" style="font-size: 1.3rem; color: {{ $session->is_current ? '#16a34a' : '#3b82f6' }};"></i>
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center gap-2">
                                            <strong style="font-size: 0.88rem; color: #0f172a;">{{ $session->device_info['label'] }}</strong>
                                            @if($session->is_current)
                                                <span class="badge bg-success" style="font-size: 0.68rem; font-weight: 600;"><i class="bi bi-check-circle-fill me-1"></i> Current Device</span>
                                            @endif
                                        </div>
                                        <div class="text-muted" style="font-size: 0.78rem; margin-top: 2px;">
                                            <i class="bi bi-globe me-1"></i> IP: <code>{{ $session->ip_address }}</code> &nbsp;•&nbsp; 
                                            <i class="bi bi-clock-history me-1"></i> Active {{ $session->last_activity }}
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    @if(!$session->is_current)
                                        <form method="POST" action="{{ route('admin.settings.logout_device', $session->id) }}" onsubmit="return confirm('Are you sure you want to log out this device ({{ $session->device_info['label'] }} - IP: {{ $session->ip_address }})?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm" style="font-size: 0.75rem; font-weight: 600; padding: 4px 10px;">
                                                <i class="bi bi-box-arrow-right me-1"></i> Log Out
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted" style="font-size: 0.75rem; font-weight: 600;"><i class="bi bi-shield-check text-success me-1"></i> Active Session</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-secondary text-center py-3 mb-3" style="font-size: 0.82rem;">
                        No active database sessions tracked.
                    </div>
                @endif

                <div class="border-top pt-3 mt-3">
                    <h6 style="font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 8px;">Primary Device Lock</h6>
                    @if(Auth::user()->admin_device_token)
                        <div class="alert alert-info d-flex align-items-center gap-2 mb-3" style="font-size: 0.82rem; border-radius: 6px; padding: 10px 14px; color: #0f172a; background-color: #f1f5f9; border: 1px solid #cbd5e1;">
                            <i class="bi bi-shield-lock-fill" style="font-size: 1.2rem; color: var(--primary);"></i>
                            <div><strong>Primary Device Token Active:</strong> Unrecognized login attempts will require multi-factor verification / approval before signing in.</div>
                        </div>
                        <form method="POST" action="{{ route('admin.settings.reset_device') }}" onsubmit="return confirm('Are you sure you want to reset your registered primary device token? You will need to re-authorize a new device using OTP.')">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-secondary w-100" style="font-weight: 600; font-size: 0.78rem;">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Primary Device Token Lock
                            </button>
                        </form>
                    @else
                        <div class="alert alert-warning d-flex align-items-center gap-2 mb-3" style="font-size: 0.82rem; border-radius: 6px; padding: 10px 14px; color: #854d0e; background-color: #fef9c3; border: 1px solid #fef08a;">
                            <i class="bi bi-exclamation-triangle-fill" style="font-size: 1.2rem; color: #ca8a04;"></i>
                            <div><strong>No Primary Device Lock:</strong> Your account does not have a primary device token locked in. Click below to lock this browser as primary.</div>
                        </div>
                        <form method="POST" action="{{ route('admin.settings.register_current_device') }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-primary w-100" style="font-weight: 600; font-size: 0.78rem;">
                                <i class="bi bi-shield-plus me-1"></i> Register & Lock This Device as Primary
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- System Information Grid Card -->
<div class="card shadow-sm border-0 mt-2 mb-4" style="border-radius: 12px; overflow: hidden;">
    <div class="card-header bg-white py-3 px-4 border-bottom">
        <h5 class="card-title mb-0" style="font-size: 1.05rem; font-weight: 700; color: #0f172a;">
            <i class="bi bi-info-circle text-primary me-2"></i>System & Environment Information
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="p-3 border rounded-3 bg-light">
                    <div style="font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase;">System Name</div>
                    <div style="font-size: 0.95rem; font-weight: 700; color: #0f172a;">SSC Transparency System</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 border rounded-3 bg-light">
                    <div style="font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Laravel Framework</div>
                    <div style="font-size: 0.95rem; font-family: monospace; font-weight: 700; color: #0f172a;">v{{ app()->version() }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 border rounded-3 bg-light">
                    <div style="font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase;">PHP Version</div>
                    <div style="font-size: 0.95rem; font-family: monospace; font-weight: 700; color: #0f172a;">v{{ PHP_VERSION }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 border rounded-3 bg-light">
                    <div style="font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Server Time</div>
                    <div style="font-size: 0.95rem; font-weight: 700; color: #0f172a;">{{ now()->format('M d, Y h:i A') }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 border rounded-3 bg-light">
                    <div style="font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Active Academic Year</div>
                    <div style="font-size: 0.95rem; font-weight: 700; color: #2563eb;">{{ \App\Helpers\SscHelper::getActiveSchoolYear() }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 border rounded-3 bg-light">
                    <div style="font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Session Driver</div>
                    <div style="font-size: 0.95rem; font-weight: 700; color: #16a34a; font-family: monospace;">{{ config('session.driver') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: OTP Verification for SQL Backup Download -->
<div class="modal fade" id="exportOtpModal" tabindex="-1" aria-labelledby="exportOtpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold" id="exportOtpModalLabel" style="font-size: 1.1rem;">
                    <i class="bi bi-shield-lock me-2"></i>Database Export Verification
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('admin.settings.export') }}">
                @csrf
                <div class="modal-body p-4 text-center">
                    <div class="mb-3">
                        <div style="width: 60px; height: 60px; border-radius: 50%; background: #eff6ff; display: inline-flex; align-items: center; justify-content: center;" class="mb-2">
                            <i class="bi bi-envelope-check-fill text-primary" style="font-size: 1.8rem;"></i>
                        </div>
                        <h6 style="font-weight: 700; color: #0f172a;">Verification Code Required</h6>
                        <p class="text-muted" style="font-size: 0.85rem;" id="otpStatusMsg">
                            A secure 6-digit verification code has been sent to <strong>{{ Auth::user()->email }}</strong>.
                        </p>
                    </div>

                    <div class="mb-3">
                        <label for="otpCodeInput" class="form-label fw-bold text-dark" style="font-size: 0.85rem;">Enter 6-Digit OTP Code</label>
                        <input type="text" name="otp" id="otpCodeInput" class="form-control form-control-lg text-center fw-bold" 
                               placeholder="123456" maxlength="6" pattern="\d{6}" required autofocus 
                               style="letter-spacing: 8px; font-size: 1.6rem; color: #1e3a8a; background: #f8fafc;">
                    </div>

                    <div style="font-size: 0.78rem;" class="text-muted mb-2">
                        <i class="bi bi-clock-history me-1"></i> Code is valid for 3 minutes.
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-4 d-flex justify-content-between">
                    <button type="button" onclick="requestExportOtp()" class="btn btn-link btn-sm text-decoration-none text-muted" style="font-weight: 600;">
                        <i class="bi bi-arrow-clockwise me-1"></i> Resend Code
                    </button>
                    <div>
                        <button type="button" class="btn btn-secondary btn-sm me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning btn-sm text-dark fw-bold px-3">
                            <i class="bi bi-download me-1"></i> Confirm & Download
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: OTP Verification for Maintenance Mode Toggle -->
<div class="modal fade" id="maintenanceOtpModal" tabindex="-1" aria-labelledby="maintenanceOtpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header text-white py-3" id="maintenanceModalHeader" style="background: #1e3a8a;">
                <h5 class="modal-title fw-bold" id="maintenanceOtpModalLabel" style="font-size: 1.1rem;">
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
                        <div id="maintenanceModalIconBg" style="width: 60px; height: 60px; border-radius: 50%; background: #eff6ff; display: inline-flex; align-items: center; justify-content: center;" class="mb-2">
                            <i id="maintenanceModalIcon" class="bi bi-cone-striped text-primary" style="font-size: 1.8rem;"></i>
                        </div>
                        <h6 style="font-weight: 700; color: #0f172a;" id="maintenanceModalActionTitle">Security Authorization Required</h6>
                        <p class="text-muted" style="font-size: 0.85rem;" id="otpMaintenanceStatusMsg">
                            A secure 6-digit verification code has been sent to <strong>{{ Auth::user()->email }}</strong>.
                        </p>
                    </div>

                    <div class="mb-3">
                        <label for="otpMaintenanceCodeInput" class="form-label fw-bold text-dark" style="font-size: 0.85rem;">Enter 6-Digit OTP Code</label>
                        <input type="text" name="otp" id="otpMaintenanceCodeInput" class="form-control form-control-lg text-center fw-bold" 
                               placeholder="123456" maxlength="6" pattern="\d{6}" required autofocus 
                               style="letter-spacing: 8px; font-size: 1.6rem; color: #1e3a8a; background: #f8fafc;">
                    </div>

                    <div style="font-size: 0.78rem;" class="text-muted mb-2">
                        <i class="bi bi-clock-history me-1"></i> Code is valid for 3 minutes.
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-4 d-flex justify-content-between">
                    <button type="button" onclick="resendMaintenanceOtp()" class="btn btn-link btn-sm text-decoration-none text-muted" style="font-weight: 600;">
                        <i class="bi bi-arrow-clockwise me-1"></i> Resend Code
                    </button>
                    <div>
                        <button type="button" class="btn btn-secondary btn-sm me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="btnConfirmMaintenance" class="btn btn-primary btn-sm fw-bold px-3">
                            <i class="bi bi-check2-circle me-1"></i> Confirm &amp; Save
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
