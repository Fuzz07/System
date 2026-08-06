@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-admin') @endsection

@section('content')
<div class="page-header"><div><h1>System Settings</h1><p>Configure school years and monitor system data</p></div></div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card"><div class="card-header-custom"><span class="card-title">School Year Management</span></div>
        <div class="card-body-custom">
            <form method="POST" action="{{ route('admin.settings.sy.add') }}" class="d-flex gap-2 mb-4">@csrf
                <input type="text" name="sy_label" class="form-control-custom flex-fill" placeholder="YYYY-YYYY" required pattern="\d{4}-\d{4}">
                <button type="submit" class="btn-primary-custom"><i class="bi bi-plus"></i> Add</button>
            </form>
            <table class="table-custom"><thead><tr><th>School Year</th><th>Status</th><th>Actions</th></tr></thead><tbody>
            @foreach($schoolYears as $sy)
            <tr>
                <td style="font-weight:700;">{{ $sy->label }}</td>
                <td>{!! $sy->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' !!}</td>
                <td>
                    @if(!$sy->is_active)
                    <form method="POST" action="{{ route('admin.settings.sy.activate', $sy) }}" class="d-inline">@csrf @method('PATCH')<button class="btn btn-success btn-sm" style="font-size:.72rem;">Set Active</button></form>
                    <form method="POST" action="{{ route('admin.settings.sy.delete', $sy) }}" class="d-inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm" style="font-size:.72rem;"><i class="bi bi-trash"></i></button></form>
                    @else <span class="text-muted" style="font-size:.78rem;">Current</span>
                    @endif
                </td>
            </tr>
            @endforeach
            </tbody></table>
        </div></div>

        <div class="card mt-4"><div class="card-header-custom"><span class="card-title">Candidacy & Filing Control</span></div>
        <div class="card-body-custom">
            @php
                $activeSy = \App\Models\SchoolYear::where('is_active', 1)->first();
            @endphp
            @if($activeSy)
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h6 class="mb-1" style="font-weight:700;color:var(--navy-900);">SSC Officer Candidacy Filing</h6>
                        <p class="text-muted mb-0" style="font-size:0.8rem;">Status: 
                            @if($activeSy->candidacy_open)
                                <span class="badge bg-success">OPEN</span>
                            @else
                                <span class="badge bg-danger">CLOSED</span>
                            @endif
                        </p>
                    </div>
                    <form method="POST" action="{{ route('admin.settings.candidacy.toggle') }}">
                        @csrf
                        <button type="submit" class="btn {{ $activeSy->candidacy_open ? 'btn-danger' : 'btn-success' }} btn-sm" style="font-weight:700;font-size:0.8rem;padding:8px 16px;">
                            {{ $activeSy->candidacy_open ? 'Close Filing' : 'Open Filing' }}
                        </button>
                    </form>
                </div>
                <p class="text-muted mb-0" style="font-size:0.75rem;line-height:1.4;">
                    <i class="bi bi-info-circle"></i> Opening filing allows students to submit candidacy applications for department representatives. Toggling will automatically post a system-wide announcement.
                </p>
            @else
                <div class="alert alert-warning mb-0" style="font-size:0.8rem;padding:10px 14px;">
                    Please set an active school year to manage candidacy filing.
                </div>
            @endif
        </div></div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header-custom d-flex justify-content-between align-items-center">
                <span class="card-title mb-0">Database Overview</span>
                <a href="{{ route('admin.settings.export') }}" class="btn btn-sm btn-outline-primary" style="font-size: 0.75rem;"><i class="bi bi-download"></i> Export SQL Backup</a>
            </div>
        <div class="card-body-custom">
            <table class="table-custom"><thead><tr><th>Table</th><th>Records</th></tr></thead><tbody>
            @foreach($dbStats as $table => $count)
            <tr><td style="font-family:monospace;font-size:.82rem;">📊 {{ $table }}</td><td><span class="badge bg-primary">{{ number_format($count) }}</span></td></tr>
            @endforeach
            </tbody></table>
        </div></div>

        <div class="card mt-4">
            <div class="card-header-custom d-flex justify-content-between align-items-center">
                <span class="card-title mb-0"><i class="bi bi-devices text-primary me-2"></i>Active Logged-in Devices</span>
                @if(count($activeSessions) > 1)
                    <form method="POST" action="{{ route('admin.settings.logout_others') }}" onsubmit="return confirm('Are you sure you want to terminate all other active device sessions? Any other device currently logged in will be instantly signed out.')">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger" style="font-size:0.75rem; font-weight: 600;">
                            <i class="bi bi-box-arrow-right"></i> Log Out All Other Devices
                        </button>
                    </form>
                @endif
            </div>
            <div class="card-body-custom">
                <p style="font-size: 0.85rem; color: #475569; line-height: 1.5; margin-bottom: 16px;">
                    Below is the list of active devices and browsers currently signed into your admin account. You can selectively log out any specific device.
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
                                        <span class="text-muted" style="font-size: 0.75rem; font-weight: 600;"><i class="bi bi-shield-check text-success"></i> Active Session</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-secondary text-center py-3 mb-3" style="font-size: 0.82rem;">
                        No active database sessions tracked. (Database session driver active).
                    </div>
                @endif

                <div class="border-top pt-3 mt-3">
                    <h6 style="font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 8px;">Primary Device Restriction</h6>
                    @if(Auth::user()->admin_device_token)
                        <div class="alert alert-info d-flex align-items-center gap-2 mb-3" style="font-size: 0.82rem; border-radius: 6px; padding: 10px 14px; color: #0f172a; background-color: #f1f5f9; border: 1px solid #cbd5e1;">
                            <i class="bi bi-shield-lock-fill" style="font-size: 1.2rem; color: var(--primary);"></i>
                            <div><strong>Primary Device Token Active:</strong> Unrecognized devices will require multi-factor verification / approval before signing in.</div>
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

<div class="card mt-4"><div class="card-header-custom"><span class="card-title">System Information</span></div>
<div class="card-body-custom"><div class="row g-3">
    <div class="col-md-4"><div style="font-size:.78rem;color:#718096;font-weight:600;">System Name</div><div style="font-size:.9rem;font-weight:700;color:var(--navy-900);">SSC Transparency System</div></div>
    <div class="col-md-4"><div style="font-size:.78rem;color:#718096;font-weight:600;">Laravel Version</div><div style="font-size:.9rem;font-family:monospace;">{{ app()->version() }}</div></div>
    <div class="col-md-4"><div style="font-size:.78rem;color:#718096;font-weight:600;">PHP Version</div><div style="font-size:.9rem;font-family:monospace;">{{ PHP_VERSION }}</div></div>
    <div class="col-md-4"><div style="font-size:.78rem;color:#718096;font-weight:600;">Server Time</div><div style="font-size:.9rem;">{{ now()->format('M d, Y h:i A') }}</div></div>
    <div class="col-md-4"><div style="font-size:.78rem;color:#718096;font-weight:600;">Active School Year</div><div style="font-size:.9rem;font-weight:700;color:var(--accent);">{{ \App\Helpers\SscHelper::getActiveSchoolYear() }}</div></div>
</div></div></div>
@endsection
