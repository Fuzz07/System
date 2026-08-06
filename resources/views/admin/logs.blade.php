@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-admin') @endsection

@php $actionIcons = ['LOGIN' => ['bi-box-arrow-in-right', 'teal'], 'LOGOUT' => ['bi-box-arrow-left', 'secondary'], 'REGISTER' => ['bi-person-plus', 'success'], 'PROPOSAL_SUBMIT' => ['bi-file-earmark-text', 'primary'], 'PROPOSAL_APPROVE' => ['bi-check2-circle', 'success'], 'PROPOSAL_REJECT' => ['bi-x-circle', 'danger'], 'EXPENSE_SUBMIT' => ['bi-receipt', 'warning'], 'EXPENSE_APPROVE' => ['bi-check2', 'success'], 'EXPENSE_REJECT' => ['bi-x', 'danger'], 'BUDGET_CREATE' => ['bi-wallet2', 'primary'], 'BUDGET_APPROVE' => ['bi-check-circle', 'success'], 'USER_ADD' => ['bi-person-plus-fill', 'success'], 'USER_DELETE' => ['bi-person-x', 'danger'], 'ANNOUNCEMENT_POST' => ['bi-megaphone', 'info'], 'LIQUIDATION_UPLOAD' => ['bi-folder-check', 'primary'], 'FEEDBACK_SUBMIT' => ['bi-chat-dots', 'info']]; @endphp

@section('content')
    <div class="page-header">
        <div>
            <h1>Activity Logs</h1>
            <p>Full system activity audit trail</p>
        </div>
        <span class="badge bg-secondary" style="font-size:.85rem;padding:8px 14px;">{{ number_format($logs->total()) }}
            total logs</span>
    </div>

    <div class="card mb-4">
        <div class="card-body-custom">
            <form method="GET" action="{{ route('admin.logs') }}" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0" style="font-size: 0.85rem;"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control form-control-sm border-start-0"
                            placeholder="Search action, details, IP, or user..." value="{{ $search }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Filter by Role (All) --</option>
                        <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>👑 Admin Logs</option>
                        <option value="student" {{ $role === 'student' ? 'selected' : '' }}>🎓 Student Logs</option>
                        <option value="officer" {{ $role === 'officer' ? 'selected' : '' }}>⭐ Officer Logs</option>
                        <option value="treasurer" {{ $role === 'treasurer' ? 'selected' : '' }}>💰 Treasurer Logs</option>
                        <option value="dean" {{ $role === 'dean' ? 'selected' : '' }}>🎓 Dean Logs</option>
                        <option value="system" {{ $role === 'system' ? 'selected' : '' }}>⚙️ System Logs</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="action_type" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Filter by Action (All) --</option>
                        @foreach($distinctActions as $act)
                            <option value="{{ $act }}" {{ $actionType === $act ? 'selected' : '' }}>{{ $act }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill" style="font-weight: 600;"><i class="bi bi-funnel"></i> Filter</button>
                    @if($search || $role || $actionType)
                        <a href="{{ route('admin.logs') }}" class="btn btn-outline-secondary btn-sm" title="Clear Filters"><i class="bi bi-x-circle"></i></a>
                    @endif
                </div>
            </form>

            <div class="d-flex flex-wrap gap-2 mt-3 pt-2 border-top align-items-center">
                <span class="text-muted" style="font-size: 0.75rem; font-weight: 600;">Quick Role Filters:</span>
                <a href="{{ route('admin.logs') }}" class="badge {{ empty($role) ? 'bg-primary' : 'bg-light text-dark border' }}" style="font-weight: 600; text-decoration: none; padding: 6px 10px;">All</a>
                <a href="{{ route('admin.logs', ['role' => 'admin', 'search' => $search, 'action_type' => $actionType]) }}" class="badge {{ $role === 'admin' ? 'bg-danger' : 'bg-light text-dark border' }}" style="font-weight: 600; text-decoration: none; padding: 6px 10px;">Admin</a>
                <a href="{{ route('admin.logs', ['role' => 'student', 'search' => $search, 'action_type' => $actionType]) }}" class="badge {{ $role === 'student' ? 'bg-success' : 'bg-light text-dark border' }}" style="font-weight: 600; text-decoration: none; padding: 6px 10px;">Student</a>
                <a href="{{ route('admin.logs', ['role' => 'officer', 'search' => $search, 'action_type' => $actionType]) }}" class="badge {{ $role === 'officer' ? 'bg-warning text-dark' : 'bg-light text-dark border' }}" style="font-weight: 600; text-decoration: none; padding: 6px 10px;">Officer</a>
                <a href="{{ route('admin.logs', ['role' => 'system', 'search' => $search, 'action_type' => $actionType]) }}" class="badge {{ $role === 'system' ? 'bg-secondary' : 'bg-light text-dark border' }}" style="font-weight: 600; text-decoration: none; padding: 6px 10px;">System</a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive-custom">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Action</th>
                        <th>User</th>
                        <th>Role</th>
                        <th>Details</th>
                        <th>IP</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $i => $log)
                        @php [$icon, $color] = $actionIcons[$log->action] ?? ['bi-activity', 'secondary']; @endphp
                        <tr>
                            <td style="color:#a0aec0;font-size:.78rem;">{{ $logs->firstItem() + $i }}</td>
                            <td><span class="badge bg-{{ $color }}" style="font-size:.72rem;"><i class="bi {{ $icon }}"></i>
                                    {{ $log->action }}</span></td>
                            <td style="font-size:.82rem;font-weight:600;">{{ $log->user->fullname ?? 'System' }}</td>
                            <td>{!! $log->user ? \App\Helpers\SscHelper::roleBadge($log->user->role) : '<span class="badge bg-secondary" style="font-size:.7rem;">System</span>' !!}
                            </td>
                            <td style="font-size:.78rem;color:#718096;max-width:280px;">{{ $log->details ?? '—' }}</td>
                            <td style="font-size:.72rem;color:#a0aec0;font-family:monospace;">{{ $log->ip_address ?? '—' }}</td>
                            <td style="font-size:.75rem;white-space:nowrap;color:#718096;">
                                {{ $log->created_at?->format('M d, Y') }}<br><span
                                    style="font-size:.7rem;">{{ $log->created_at?->format('h:i A') }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No activity logs found matching the filter criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="card-footer bg-white d-flex justify-content-between align-items-center py-2 px-3">
                <div style="font-size: 0.8rem;" class="text-muted">
                    Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ number_format($logs->total()) }} entries
                </div>
                <div>
                    {{ $logs->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection