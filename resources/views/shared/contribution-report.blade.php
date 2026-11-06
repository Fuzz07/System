@extends('layouts.app')

@section('sidebar-nav') @include('partials.sidebar-' . $portal) @endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-file-earmark-bar-graph me-2 text-primary"></i>Contribution Fee Reports</h1>
            <p class="text-muted mb-0">
                SSC monthly dues collection
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
                <div class="col-md-3">
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
                <div class="col-md-3">
                    <label for="year_level" class="form-label fw-semibold">Year level</label>
                    <select class="form-select" name="year_level" id="year_level" data-auto-filter-control>
                        <option value="">All Years</option>
                        @foreach($yearLevelOptions as $option)
                            <option value="{{ $option }}" @selected($yearLevel === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="section" class="form-label fw-semibold">Section</label>
                    <select class="form-select" name="section" id="section" data-auto-filter-control>
                        <option value="">All Sections</option>
                        @foreach($sectionOptions as $option)
                            <option value="{{ $option }}" @selected($section === $option)>{{ $option }}</option>
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
                'section' => $section,
            ]);
        @endphp
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <div class="fw-bold">SSC MONTHLY DUES - {{ $term->academic_term }}</div>
                <small class="text-muted">
                    DATE: {{ now()->format('m/d/y') }}
                    &middot; {{ $payments->count() }} paid contribution{{ $payments->count() === 1 ? '' : 's' }}
                </small>
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

        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 70px;">No.</th>
                            <th>Name</th>
                            <th>Section &amp; Year</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr>
                                <td>{{ $loop->iteration }}.</td>
                                <td>{{ $payment->user->fullname }}</td>
                                <td>{{ trim(($payment->user->section ? $payment->user->section . ' - ' : '') . ($payment->user->year_level ?? '')) ?: '—' }}</td>
                                <td class="text-end fw-semibold">{!! \App\Helpers\SscHelper::formatCurrency($payment->amount) !!}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">No paid contributions match these filters.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="3">Total</td>
                            <td class="text-end">{!! \App\Helpers\SscHelper::formatCurrency($totalCollected) !!}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @else
        <div class="alert alert-info">No academic terms are available for a contribution report.</div>
    @endif
</div>
@endsection
