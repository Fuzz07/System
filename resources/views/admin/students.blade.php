@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-admin') @endsection

@section('sidebar-nav')
    @include('layouts.app')
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Student Accounts</h4>
            <small class="text-muted">Total: {{ $totalStudents }} • Active: {{ $activeStudents }} • Pending: {{ $pendingStudents }}</small>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="p-3">
                <form class="row g-2 mb-3" method="GET">
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
                        <select name="year_level" class="form-select">
                            <option value="">All Years</option>
                            @foreach($years as $y)
                                <option value="{{ $y }}" {{ (isset($yearLevel) && $yearLevel == $y) ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
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
                            <th>Year</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td>{{ $user->fullname }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->student_id }}</td>
                            <td>{{ $user->department }}</td>
                            <td>{{ $user->year_level }}</td>
                            <td>
                                <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'warning' }}">{{ ucfirst($user->status) }}</span>
                            </td>
                            <td class="text-end">
                                @if($user->status === 'inactive')
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

                                <form action="{{ route('admin.students.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this account?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
