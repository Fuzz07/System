@extends('layouts.app')

@section('sidebar-nav') @include('partials.sidebar-' . $portal) @endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-file-earmark-bar-graph me-2 text-primary"></i>Contribution Fee Reports</h1>
            <p class="text-muted mb-0">
                Review paid contribution fees by department and year level.
                @if($term) <span class="fw-semibold">{{ $term->academic_term }}</span> @endif
            </p>
        </div>
        <a href="{{ route($portal . '.enrollment.payments') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Contribution Fees
        </a>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route($portal . '.enrollment.payments.reports') }}"
                  class="row g-3 align-items-end" data-auto-filter>
                <div class="col-md-5">
                    <label for="term" class="form-label fw-semibold">Academic term</label>
                    <select class="form-select" name="term" id="term" data-auto-filter-control>
                        @forelse($terms as $option)
                            <option value="{{ $option->id }}" @selected($term && $term->id === $option->id)>
                                {{ $option->academic_term }}{{ $option->is_active ? ' (Active)' : '' }}
                            </option>
                        @empty
                            <option value="">No academic terms available</option>
                        @endforelse
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="department" class="form-label fw-semibold">Department</label>
                    <select class="form-select" name="department" id="department" data-auto-filter-control>
                        <option value="">All Departments</option>
                        @foreach($departmentOptions as $option)
                            <option value="{{ $option }}" @selected($department === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="year_level" class="form-label fw-semibold">Year level</label>
                    <select class="form-select" name="year_level" id="year_level" data-auto-filter-control>
                        <option value="">All Years</option>
                        @foreach($yearLevelOptions as $option)
                            <option value="{{ $option }}" @selected($yearLevel === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    @if($term)
        @php
            $reportQuery = array_filter([
                'term' => $term->id,
                'department' => $department,
                'year_level' => $yearLevel,
            ]);
        @endphp
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <div class="fw-bold">{{ $term->academic_term }}</div>
                <small class="text-muted">Showing confirmed paid contributions matching the selected filters.</small>
            </div>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-secondary" target="_blank"
                   href="{{ route($portal . '.enrollment.payments.reports.print', $reportQuery) }}">
                    <i class="bi bi-printer me-1"></i> Print
                </a>
                <a class="btn btn-success"
                   href="{{ route($portal . '.enrollment.payments.reports.download', $reportQuery) }}">
                    <i class="bi bi-download me-1"></i> Download CSV
                </a>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="small text-muted">Students paid</div>
                        <div class="fs-3 fw-bold">{{ $payments->pluck('user_id')->unique()->count() }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="small text-muted">Payment records</div>
                        <div class="fs-3 fw-bold">{{ $payments->count() }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="small text-muted">Total collected</div>
                        <div class="fs-3 fw-bold text-success">{!! \App\Helpers\SscHelper::formatCurrency($totalCollected) !!}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">Breakdown by Department and Year Level</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Department</th>
                            <th>Year Level</th>
                            <th class="text-end">Students Paid</th>
                            <th class="text-end">Payment Records</th>
                            <th class="text-end">Collected</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($breakdown as $row)
                            <tr>
                                <td>{{ $row['department'] }}</td>
                                <td>{{ $row['year_level'] }}</td>
                                <td class="text-end">{{ $row['students'] }}</td>
                                <td class="text-end">{{ $row['payments'] }}</td>
                                <td class="text-end fw-semibold">{!! \App\Helpers\SscHelper::formatCurrency($row['collected']) !!}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No paid contributions match these filters.</td></tr>
                        @endforelse
                    </tbody>
                    @if($breakdown->isNotEmpty())
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="2">Total</td>
                                <td class="text-end">{{ $payments->pluck('user_id')->unique()->count() }}</td>
                                <td class="text-end">{{ $payments->count() }}</td>
                                <td class="text-end">{!! \App\Helpers\SscHelper::formatCurrency($totalCollected) !!}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold">Paid Contribution Details</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Student</th>
                            <th>Student ID</th>
                            <th>Department</th>
                            <th>Year Level</th>
                            <th>Payment Date</th>
                            <th>Reference</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr>
                                <td>{{ $payment->user->fullname }}</td>
                                <td>{{ $payment->user->student_id }}</td>
                                <td>{{ $payment->user->department ?: '—' }}</td>
                                <td>{{ $payment->user->year_level ?: '—' }}</td>
                                <td>{{ ($payment->paid_at ?? $payment->created_at)?->format('M d, Y') ?? '—' }}</td>
                                <td>{{ $payment->reference ?: '—' }}</td>
                                <td class="text-end fw-semibold">{!! \App\Helpers\SscHelper::formatCurrency($payment->amount) !!}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No paid contribution details to display.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="alert alert-info">No academic terms are available for a contribution report.</div>
    @endif
</div>
@endsection
