@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-admin') @endsection

@section('content')
<div class="page-header">
    <div><h1>Manage Officers</h1><p>View, create, and manage officers</p></div>
    <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addUserModal"><i class="bi bi-person-plus"></i> Add Officer</button>
</div>

<div class="card mb-4"><div class="card-body-custom">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-5"><input type="text" name="search" class="form-control-custom" placeholder="Search name or email..." value="{{ $search }}"></div>
        <div class="col-md-3"><select name="role_filter" class="form-select-custom"><option value="">All Roles</option>@foreach(['officer','treasurer'] as $r)<option value="{{ $r }}" {{ $roleFilter === $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>@endforeach</select></div>
        <div class="col-md-4 d-flex gap-2"><button type="submit" class="btn-primary-custom flex-fill justify-content-center"><i class="bi bi-search"></i></button><a href="{{ route('admin.officers') }}" class="btn btn-outline-secondary">Reset</a></div>
    </form>
</div></div>

<div class="card"><div class="card-header-custom"><span class="card-title">All Officers</span><span class="badge bg-secondary">{{ count($users) }} records</span></div>
<div class="table-responsive-custom"><table class="table-custom">
    <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Dept</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
    <tbody>
    @forelse($users as $u)
    <tr>
        <td style="font-weight:700;">{{ $u->fullname }}</td>
        <td style="font-size:.82rem;">{{ $u->email }}</td>
        <td>{!! \App\Helpers\SscHelper::roleBadge($u->role) !!}</td>
        <td>{{ $u->department ?? '—' }}</td>
        <td>{!! \App\Helpers\SscHelper::statusBadge($u->status) !!}</td>
        <td style="font-size:.78rem;white-space:nowrap;">{{ $u->created_at?->format('M d, Y') }}</td>
        <td>
            @if($u->id !== Auth::id())
            <div class="d-flex gap-1 flex-wrap">
                <form method="POST" action="{{ route('admin.officers.role', $u) }}" class="d-inline">@csrf @method('PATCH')
                    <select name="new_role" class="form-select-custom d-inline" style="width:auto;padding:4px 8px;font-size:.72rem;" onchange="this.form.submit()">
                        @foreach(['officer','treasurer'] as $r)<option value="{{ $r }}" {{ $u->role === $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>@endforeach
                    </select>
                </form>
                <form method="POST" action="{{ route('admin.officers.toggle', $u) }}" class="d-inline">@csrf @method('PATCH')
                    <button class="btn-sm-action btn {{ $u->status === 'active' ? 'btn-warning' : 'btn-success' }}" style="font-size:.72rem;"><i class="bi {{ $u->status === 'active' ? 'bi-pause-circle' : 'bi-play-circle' }}"></i></button>
                </form>
                <form method="POST" action="{{ route('admin.officers.destroy', $u) }}" class="d-inline" onsubmit="return confirm('Delete this user permanently?')">@csrf @method('DELETE')
                    <button class="btn-sm-action btn btn-outline-danger" style="font-size:.72rem;"><i class="bi bi-trash"></i></button>
                </form>
            </div>
            @else <span class="badge bg-secondary" style="font-size:.68rem;">You</span>
            @endif
        </td>
    </tr>
    @empty
    <tr><td colspan="7" class="text-center py-4 text-muted">No users found.</td></tr>
    @endforelse
    </tbody>
</table></div></div>

{{-- Add User Modal --}}
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-lg"><div class="modal-content" style="border-radius:var(--radius);border:none;">
    <div class="modal-header modal-header-custom"><h5 class="modal-title" style="font-weight:700;"><i class="bi bi-person-plus"></i> Add New Officer</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST" action="{{ route('admin.officers.store') }}">@csrf
        <div class="modal-body p-4">
            <div class="row g-2 mb-3">
                <div class="col-md-4"><label class="form-label-custom">First Name</label><input type="text" name="first_name" class="form-control-custom" required></div>
                <div class="col-md-4"><label class="form-label-custom">Middle Name</label><input type="text" name="middle_name" class="form-control-custom"></div>
                <div class="col-md-4"><label class="form-label-custom">Last Name</label><input type="text" name="last_name" class="form-control-custom" required></div>
            </div>
            <div class="row g-2 mb-3">
                <div class="col-md-4"><label class="form-label-custom">Age</label><input type="number" name="age" class="form-control-custom" min="10" required></div>
                <div class="col-md-4"><label class="form-label-custom">Year Level</label><select name="year_level" class="form-select-custom" required><option value="">Select</option>@foreach(['1st Year','2nd Year','3rd Year','4th Year','5th Year'] as $y)<option>{{ $y }}</option>@endforeach</select></div>
                <div class="col-md-4"><label class="form-label-custom">Department</label><input type="text" name="department" class="form-control-custom" placeholder="e.g. BSIT" required></div>
            </div>
            <div class="row g-2 mb-3">
                <div class="col-md-6"><label class="form-label-custom">Student ID</label><input type="text" name="student_id" class="form-control-custom" pattern="\d{4}-\d{4}" placeholder="YYYY-XXXX" required></div>
                <div class="col-md-6"><label class="form-label-custom">MS Account</label><input type="email" name="email" class="form-control-custom" placeholder="user@mcclawis.edu.ph" required></div>
            </div>
            <div class="row g-2 mb-3">
                <div class="col-md-6"><label class="form-label-custom">Password</label><input type="password" name="password" class="form-control-custom" minlength="6" required></div>
                <div class="col-md-6"><label class="form-label-custom">Role</label><select name="role" class="form-select-custom">@foreach(['officer','treasurer'] as $r)<option value="{{ $r }}">{{ ucfirst($r) }}</option>@endforeach</select></div>
            </div>
        </div>
        <div class="modal-footer border-0 pt-0">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn-primary-custom"><i class="bi bi-check2"></i> Create Officer</button>
        </div>
    </form>
</div></div></div>
@endsection
