@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-admin') @endsection

@php $actionIcons = ['LOGIN'=>['bi-box-arrow-in-right','teal'],'LOGOUT'=>['bi-box-arrow-left','secondary'],'REGISTER'=>['bi-person-plus','success'],'PROPOSAL_SUBMIT'=>['bi-file-earmark-text','primary'],'PROPOSAL_APPROVE'=>['bi-check2-circle','success'],'PROPOSAL_REJECT'=>['bi-x-circle','danger'],'EXPENSE_SUBMIT'=>['bi-receipt','warning'],'EXPENSE_APPROVE'=>['bi-check2','success'],'EXPENSE_REJECT'=>['bi-x','danger'],'BUDGET_CREATE'=>['bi-wallet2','primary'],'BUDGET_APPROVE'=>['bi-check-circle','success'],'USER_ADD'=>['bi-person-plus-fill','success'],'USER_DELETE'=>['bi-person-x','danger'],'ANNOUNCEMENT_POST'=>['bi-megaphone','info'],'LIQUIDATION_UPLOAD'=>['bi-folder-check','primary'],'FEEDBACK_SUBMIT'=>['bi-chat-dots','info']]; @endphp

@section('content')
<div class="page-header"><div><h1>Activity Logs</h1><p>Full system activity audit trail</p></div>
    <span class="badge bg-secondary" style="font-size:.85rem;padding:8px 14px;">{{ number_format($logs->total()) }} total logs</span>
</div>

<div class="card mb-4"><div class="card-body-custom">
    <form method="GET" class="d-flex gap-2">
        <input type="text" name="search" class="form-control-custom flex-fill" placeholder="Search action, details, or user..." value="{{ $search }}">
        <button type="submit" class="btn-primary-custom"><i class="bi bi-search"></i></button>
        @if($search)<a href="{{ route('admin.logs') }}" class="btn btn-outline-secondary">Clear</a>@endif
    </form>
</div></div>

<div class="card"><div class="table-responsive-custom"><table class="table-custom">
    <thead><tr><th>#</th><th>Action</th><th>User</th><th>Role</th><th>Details</th><th>IP</th><th>Time</th></tr></thead>
    <tbody>
    @forelse($logs as $i => $log)
    @php [$icon, $color] = $actionIcons[$log->action] ?? ['bi-activity', 'secondary']; @endphp
    <tr>
        <td style="color:#a0aec0;font-size:.78rem;">{{ $logs->firstItem() + $i }}</td>
        <td><span class="badge bg-{{ $color }}" style="font-size:.72rem;"><i class="bi {{ $icon }}"></i> {{ $log->action }}</span></td>
        <td style="font-size:.82rem;font-weight:600;">{{ $log->user->fullname ?? 'System' }}</td>
        <td>{!! $log->user ? \App\Helpers\SscHelper::roleBadge($log->user->role) : '<span class="text-muted">—</span>' !!}</td>
        <td style="font-size:.78rem;color:#718096;max-width:250px;">{{ $log->details ?? '—' }}</td>
        <td style="font-size:.72rem;color:#a0aec0;font-family:monospace;">{{ $log->ip_address ?? '—' }}</td>
        <td style="font-size:.75rem;white-space:nowrap;color:#718096;">{{ $log->created_at?->format('M d, Y') }}<br><span style="font-size:.7rem;">{{ $log->created_at?->format('h:i A') }}</span></td>
    </tr>
    @empty
    <tr><td colspan="7" class="text-center py-4 text-muted">No logs found.</td></tr>
    @endforelse
    </tbody>
</table></div>
@if($logs->hasPages())<div class="px-4 py-3 d-flex justify-content-center">{{ $logs->withQueryString()->links() }}</div>@endif
</div>
@endsection
