@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-admin') @endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Enrollment Payments</h4>
    </div>

    @php
        $totals = ['students' => 0, 'paid' => 0, 'pending' => 0, 'unpaid' => 0, 'collected' => 0, 'allocated' => 0, 'remaining' => 0];
        foreach ($distribution as $row) {
            foreach ($totals as $key => $value) { $totals[$key] += $row[$key]; }
        }
    @endphp

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="mb-0">Budget Distribution by Department</h6>
                    <small class="text-muted">Enrollment fees collected for {{ $currentSy }}, credited to each department's own budget.</small>
                </div>
                <a href="{{ route('admin.budgets') }}" class="btn btn-sm btn-outline-primary">Open Budgets</a>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Department</th>
                            <th class="text-end">Students</th>
                            <th class="text-end">Paid</th>
                            <th class="text-end">Pending</th>
                            <th class="text-end">Unpaid</th>
                            <th class="text-end">Collected</th>
                            <th class="text-end">Budget Allocated</th>
                            <th class="text-end">Budget Remaining</th>
                            <th style="width:120px;">Collection Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($distribution as $row)
                        @php
                            $rate = $row['students'] > 0 ? round($row['paid'] / $row['students'] * 100) : 0;
                            $mismatch = abs($row['collected'] - $row['allocated']) >= 0.01;
                        @endphp
                        <tr>
                            <td class="fw-semibold">
                                {{ $row['department'] }}
                                @if($mismatch)
                                    <i class="bi bi-exclamation-triangle-fill text-warning ms-1"
                                       title="Collected payments and the department budget do not match. Run: php artisan ssc:split-enrollment-budgets --apply"></i>
                                @endif
                            </td>
                            <td class="text-end">{{ $row['students'] }}</td>
                            <td class="text-end text-success">{{ $row['paid'] }}</td>
                            <td class="text-end text-warning">{{ $row['pending'] }}</td>
                            <td class="text-end text-muted">{{ $row['unpaid'] }}</td>
                            <td class="text-end">{!! \App\Helpers\SscHelper::formatCurrency($row['collected']) !!}</td>
                            <td class="text-end">{!! \App\Helpers\SscHelper::formatCurrency($row['allocated']) !!}</td>
                            <td class="text-end">{!! \App\Helpers\SscHelper::formatCurrency($row['remaining']) !!}</td>
                            <td>
                                <div class="progress" style="height:6px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $rate }}%"
                                         aria-valuenow="{{ $rate }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <small class="text-muted">{{ $rate }}%</small>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="9" class="text-center text-muted py-3">No departments to summarize yet.</td></tr>
                        @endforelse
                    </tbody>
                    @if(count($distribution))
                    <tfoot>
                        <tr class="fw-bold border-top">
                            <td>All Departments</td>
                            <td class="text-end">{{ $totals['students'] }}</td>
                            <td class="text-end">{{ $totals['paid'] }}</td>
                            <td class="text-end">{{ $totals['pending'] }}</td>
                            <td class="text-end">{{ $totals['unpaid'] }}</td>
                            <td class="text-end">{!! \App\Helpers\SscHelper::formatCurrency($totals['collected']) !!}</td>
                            <td class="text-end">{!! \App\Helpers\SscHelper::formatCurrency($totals['allocated']) !!}</td>
                            <td class="text-end">{!! \App\Helpers\SscHelper::formatCurrency($totals['remaining']) !!}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
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
                                    <a href="{{ \App\Helpers\SscHelper::getUploadUrl($payment->proof_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary mb-1">View Proof</a>
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
            {{ $students->withQueryString()->links('partials.pagination') }}
        </div>
    </div>
</div>
@endsection
