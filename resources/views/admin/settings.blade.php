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
