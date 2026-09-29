@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-admin') @endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Student Accounts</h4>
            <small class="text-muted">Total: {{ $totalStudents }} • Active: {{ $activeStudents }} • Pending: {{ $pendingStudents }} • Archived: {{ $archivedStudents }}</small>
        </div>
    </div>

    <ul class="nav nav-pills nav-brand d-inline-flex gap-1 bg-white border rounded-3 p-1 mb-3 shadow-sm">
        <li class="nav-item">
            <a href="{{ route('admin.students.index') }}" class="nav-link d-flex align-items-center gap-2 {{ !$showArchived ? 'active' : '' }}"><i class="bi bi-people"></i> Current Students</a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.students.index', ['view' => 'archived']) }}" class="nav-link d-flex align-items-center gap-2 {{ $showArchived ? 'active' : '' }}"><i class="bi bi-archive"></i> Archived / Graduated ({{ $archivedStudents }})</a>
        </li>
    </ul>

    @if($showArchived)
    <div class="alert alert-light border small mb-3">
        <i class="bi bi-info-circle me-1"></i> 4th-year students are archived automatically when a later school year is set active in Settings. Restore anyone who has not actually graduated.
    </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="p-3">
                <form class="row g-2 mb-3" method="GET">
                    @if($showArchived)<input type="hidden" name="view" value="archived">@endif
                    <div class="col-md-4">
                        <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control" placeholder="Search by name, email, or student id">
                    </div>
                    <div class="col-md-3">
                        <select name="department" class="form-select">
                            <option value="">All Departments</option>
                            @foreach($departments as $d)
                                <option value="{{ $d }}" {{ (isset($department) && $department == $d) ? 'selected' : '' }}>{{ $d }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        @if($showArchived)
                        <select name="batch" class="form-select">
                            <option value="">All Batches</option>
                            @foreach($batches as $b)
                                <option value="{{ $b }}" {{ $batch == $b ? 'selected' : '' }}>SY {{ $b }}</option>
                            @endforeach
                        </select>
                        @else
                        <select name="year_level" class="form-select">
                            <option value="">All Years</option>
                            @foreach($years as $y)
                                <option value="{{ $y }}" {{ (isset($yearLevel) && $yearLevel == $y) ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                        @endif
                    </div>
                    <div class="col-md-3 text-end">
                        <button class="btn btn-primary">Filter</button>
                    </div>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Student ID</th>
                            <th>Department</th>
                            @if($showArchived)
                            <th>Graduated</th>
                            <th>Archived On</th>
                            @else
                            <th>Year</th>
                            <th>Status</th>
                            @endif
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td>{{ $user->fullname }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->student_id }}</td>
                            <td>{{ $user->department }}</td>
                            @if($showArchived)
                            <td>{{ $user->graduated_school_year ? 'SY ' . $user->graduated_school_year : $user->year_level }}</td>
                            <td>{{ $user->archived_at?->format('M d, Y') }}</td>
                            @else
                            <td>{{ $user->year_level }}</td>
                            <td>
                                <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'warning' }}">{{ ucfirst($user->status) }}</span>
                            </td>
                            @endif
                            <td class="text-end">
                                @if($showArchived)
                                <form action="{{ route('admin.students.restore', $user) }}" method="POST" class="d-inline" data-confirm="Restore {{ $user->fullname }} as an active 4th-year student?">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm btn-outline-primary">Restore</button>
                                </form>
                                @elseif($user->status === 'inactive')
                                <form action="{{ route('admin.students.approve', $user) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm btn-primary">Approve</button>
                                </form>
                                @else
                                <form action="{{ route('admin.students.toggle', $user) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm btn-warning">Deactivate</button>
                                </form>
                                @endif

                                <form action="{{ route('admin.students.destroy', $user) }}" method="POST" class="d-inline" data-confirm="Delete this account?">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">{{ $showArchived ? 'No archived students yet.' : 'No students found.' }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $users->withQueryString()->links('partials.pagination') }}
        </div>
    </div>
</div>
@endsection
