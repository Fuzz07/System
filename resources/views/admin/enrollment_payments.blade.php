@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-' . $portal) @endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Semester Enrollment Payments</h4>
            <small class="text-muted">Current term: {{ $currentSy }}</small>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStudentModal"><i class="bi bi-person-plus"></i> Add Student</button>
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
                @if($portal === 'admin')
                <a href="{{ route('admin.budgets') }}" class="btn btn-sm btn-outline-primary">Open Budgets</a>
                @endif
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
                                    <form method="POST" action="{{ route($portal . '.enrollment.payments.mark_paid', $payment) }}" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-success">Mark Paid</button>
                                    </form>
                                    @if($payment && $payment->proof_path && $payment->proof_status === 'pending')
                                        <form method="POST" action="{{ route($portal . '.enrollment.payments.proof.approve', $payment) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-primary">Approve Proof</button>
                                        </form>
                                        <form method="POST" action="{{ route($portal . '.enrollment.payments.proof.reject', $payment) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-danger">Reject Proof</button>
                                        </form>
                                    @endif
                                @endif
                                @if(!$payment)
                                    <form method="POST" action="{{ route($portal . '.enrollment.payments.walk_in', $student) }}" class="d-inline">
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

{{-- Add Student Modal --}}
@php
    $fromAddStudent = old('add_student_form') === '1';
    $paymentStatus = $fromAddStudent ? old('payment_status', 'unpaid') : 'unpaid';
@endphp
<div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="addStudentModalLabel"><i class="bi bi-person-plus"></i> Add Student</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="POST" action="{{ route($portal . '.enrollment.payments.students.store') }}">
            @csrf
            <input type="hidden" name="add_student_form" value="1">
            <div class="modal-body">
                @if($fromAddStudent && $errors->any())
                <div class="alert alert-danger py-2 small" role="alert">
                    <ul class="mb-0 ps-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
                @endif
                <p class="text-muted small mb-3">For students who don't have an account yet. The account is created active, and the student can sign in later by using <strong>Forgot Password</strong> with their school email.</p>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">First Name <span class="text-danger">*</span></label>
                        <input type="text" name="first_name" class="form-control" value="{{ $fromAddStudent ? old('first_name') : '' }}" maxlength="100" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Middle Name</label>
                        <input type="text" name="middle_name" class="form-control" value="{{ $fromAddStudent ? old('middle_name') : '' }}" maxlength="100">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Last Name <span class="text-danger">*</span></label>
                        <input type="text" name="last_name" class="form-control" value="{{ $fromAddStudent ? old('last_name') : '' }}" maxlength="100" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Student ID <span class="text-danger">*</span></label>
                        <input type="text" name="student_id" class="form-control" value="{{ $fromAddStudent ? old('student_id') : '' }}" placeholder="2024-0001" pattern="\d{4}-\d{4}" title="Format: YYYY-XXXX" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">School Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" value="{{ $fromAddStudent ? old('email') : '' }}" placeholder="name@mcclawis.edu.ph" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Department <span class="text-danger">*</span></label>
                        <select name="department" class="form-select" required>
                            <option value="">Select department...</option>
                            @foreach($departmentOptions as $option)
                            <option value="{{ $option }}" @selected($fromAddStudent && old('department') === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Year Level <span class="text-danger">*</span></label>
                        <select name="year_level" class="form-select" required>
                            <option value="">Select year level...</option>
                            @foreach($yearLevelOptions as $option)
                            <option value="{{ $option }}" @selected($fromAddStudent && old('year_level') === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label d-block">Payment Status for {{ $currentSy }} <span class="text-danger">*</span></label>
                        <div class="btn-group w-100" role="group" aria-label="Payment status">
                            <input type="radio" class="btn-check" name="payment_status" id="paymentStatusUnpaid" value="unpaid" @checked($paymentStatus === 'unpaid') data-payment-status>
                            <label class="btn btn-outline-warning" for="paymentStatusUnpaid">Unpaid</label>
                            <input type="radio" class="btn-check" name="payment_status" id="paymentStatusPaid" value="paid" @checked($paymentStatus === 'paid') data-payment-status>
                            <label class="btn btn-outline-success" for="paymentStatusPaid">Paid ({{ \App\Helpers\SscHelper::formatCurrency(config('ssc.enrollment_fee_amount', 50)) }})</label>
                        </div>
                    </div>
                    <div class="col-md-6" id="paymentReferenceField">
                        <label class="form-label">OR / Reference No.</label>
                        <input type="text" name="reference" class="form-control" value="{{ $fromAddStudent ? old('reference') : '' }}" maxlength="100" placeholder="Leave blank to generate one">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Student</button>
            </div>
        </form>
    </div></div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    // The OR/Reference No. only applies when the student pays now.
    const referenceField = document.getElementById('paymentReferenceField');
    const sync = () => {
        referenceField.hidden = !document.getElementById('paymentStatusPaid').checked;
    };
    document.querySelectorAll('[data-payment-status]').forEach((radio) => radio.addEventListener('change', sync));
    sync();

    @if($errors->any() && $fromAddStudent)
    bootstrap.Modal.getOrCreateInstance(document.getElementById('addStudentModal')).show();
    @endif
})();
</script>
@endpush
