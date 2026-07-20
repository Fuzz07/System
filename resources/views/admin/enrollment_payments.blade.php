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
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="all" {{ ($status ?? 'all') === 'all' ? 'selected' : '' }}>All Statuses</option>
                        <option value="unpaid" {{ ($status ?? '') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        <option value="pending" {{ ($status ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paid" {{ ($status ?? '') === 'paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                </div>
                    <div class="col-md-1 text-end">
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
                            <th>Payment Status</th>
                            <th>Reference</th>
                            <th>Proof</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                        @php
                            $payment = $student->enrollmentPayments->first();
                            $status = $payment ? ucfirst($payment->status) : 'Unpaid';
                            $proofStatus = $payment ? ucfirst($payment->proof_status ?? 'none') : 'None';
                        @endphp
                        <tr>
                            <td>{{ $student->fullname }}</td>
                            <td>{{ $student->student_id }}</td>
                            <td>{{ $student->department }}</td>
                            <td>{{ $student->year_level }}</td>
                            <td><span class="badge bg-{{ $payment && $payment->status === 'paid' ? 'success' : 'warning' }}">{{ $status }}</span></td>
                            <td>{{ $payment->reference ?? '—' }}</td>
                            <td>
                                @if($payment && $payment->proof_path)
                                    <a href="{{ asset('storage/' . $payment->proof_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary mb-1">View Proof</a>
                                    <div><small class="text-muted">{{ $proofStatus }}</small></div>
                                @else
                                    <span class="text-muted">{{ $proofStatus }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($payment && $payment->status !== 'paid')
                                    <form method="POST" action="{{ route('admin.enrollment.payments.mark_paid', $payment) }}" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-success">Mark Paid</button>
                                    </form>
                                    @if($payment && $payment->proof_path && $payment->proof_status === 'pending')
                                        <form method="POST" action="{{ route('admin.enrollment.payments.proof.approve', $payment) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-primary">Approve Proof</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.enrollment.payments.proof.reject', $payment) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-danger">Reject Proof</button>
                                        </form>
                                    @endif
                                @endif
                                @if(!$payment)
                                    <form method="POST" action="{{ route('admin.enrollment.payments.walk_in', $student) }}" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-success">Walk-in Paid</button>
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
