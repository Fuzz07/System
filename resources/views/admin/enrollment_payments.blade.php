@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-admin') @endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Enrollment Payments</h4>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form class="row g-2 mb-3" method="GET">
                <div class="col-md-4">
                    <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control" placeholder="Search by name or student id">
                </div>
                <div class="col-md-3">
                    <select name="department" class="form-select">
                        <option value="">All Departments</option>
                        @foreach($departments as $d)
                            <option value="{{ $d }}" {{ ($dept ?? '') == $d ? 'selected' : '' }}>{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="year_level" class="form-select">
                        <option value="">All Years</option>
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ ($year ?? '') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 text-end">
                    <button class="btn btn-primary">Filter</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Student ID</th>
                            <th>Department</th>
                            <th>Year</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $p)
                        <tr>
                            <td>{{ $p->user->fullname }}</td>
                            <td>{{ $p->user->student_id }}</td>
                            <td>{{ $p->user->department }}</td>
                            <td>{{ $p->user->year_level }}</td>
                            <td>{{ \App\Helpers\SscHelper::formatCurrency($p->amount) }}</td>
                            <td>{{ ucfirst($p->method) }}</td>
                            <td><span class="badge bg-{{ $p->status === 'paid' ? 'success' : 'warning' }}">{{ ucfirst($p->status) }}</span></td>
                            <td class="text-end">
                                @if($p->status !== 'paid')
                                <form method="POST" action="{{ route('admin.enrollment.payments.mark_paid', $p) }}" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-success">Mark Paid (Walk-in)</button>
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
@endsection
