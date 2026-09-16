@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-admin') @endsection

@section('content')
<style>
.print-budget-report { display: none; }

@media print {
    @page { size: A4 landscape; margin: 10mm; }
    body { background-color: white !important; }
    .sidebar, .topbar, .sidebar-overlay, .alert, .budget-screen-content, .modal { display: none !important; }
    .main-wrapper { margin-left: 0 !important; width: 100% !important; padding: 0 !important; }
    .page-content { padding: 0 !important; overflow: visible !important; height: auto !important; }
    .print-budget-report {
        display: block !important;
        color: #111827;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 9pt;
        line-height: 1.35;
        width: 100%;
    }
    .print-report-header {
        align-items: center;
        border-bottom: 2px solid #0f172a;
        display: flex;
        justify-content: space-between;
        margin-bottom: 12px;
        padding-bottom: 9px;
    }
    .print-report-brand { align-items: center; display: flex; gap: 10px; }
    .print-report-logo { height: 48px; object-fit: contain; width: 48px; }
    .print-report-title { font-size: 16pt; font-weight: 700; letter-spacing: .2px; margin: 0; }
    .print-report-subtitle { color: #475569; font-size: 8pt; margin-top: 2px; }
    .print-report-meta { color: #475569; font-size: 8pt; text-align: right; }
    .print-summary-table { border-collapse: separate; border-spacing: 6px 0; margin: 0 -6px 13px; table-layout: fixed; width: calc(100% + 12px); }
    .print-summary-table td { border: 1px solid #cbd5e1; padding: 7px 9px; vertical-align: top; }
    .print-summary-label { color: #64748b; font-size: 7pt; font-weight: 700; letter-spacing: .4px; text-transform: uppercase; }
    .print-summary-value { color: #0f172a; font-size: 12pt; font-weight: 700; margin-top: 2px; }
    .print-section { margin-top: 12px; page-break-inside: auto; }
    .print-section-title { border-bottom: 1px solid #94a3b8; font-size: 10pt; font-weight: 700; margin: 0 0 5px; padding-bottom: 3px; }
    .print-table { border-collapse: collapse; table-layout: fixed; width: 100%; }
    .print-table th, .print-table td { border: 1px solid #cbd5e1; padding: 5px 6px; vertical-align: top; }
    .print-table th { background: #e2e8f0 !important; color: #0f172a; font-size: 7.5pt; font-weight: 700; text-transform: uppercase; }
    .print-table thead { display: table-header-group; }
    .print-table tfoot { display: table-footer-group; }
    .print-table tr { page-break-inside: avoid; }
    .print-table .amount { font-variant-numeric: tabular-nums; text-align: right; white-space: nowrap; }
    .print-table .center { text-align: center; }
    .print-budget-note { color: #64748b; font-size: 7pt; margin-top: 1px; overflow-wrap: anywhere; }
    .print-status { font-size: 7pt; font-weight: 700; letter-spacing: .2px; text-transform: uppercase; }
    .print-filter-line { color: #475569; font-size: 7.5pt; margin: -4px 0 9px; }
    .print-signatures { display: flex; gap: 60px; justify-content: flex-end; margin-top: 28px; page-break-inside: avoid; }
    .print-signature { border-top: 1px solid #334155; min-width: 180px; padding-top: 4px; text-align: center; }
    .print-signature small { color: #64748b; display: block; font-size: 7pt; }
    * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
}
</style>

@php
    $printApprovedBudgets = $budgets->where('status', 'Approved');
    $printAllocated = (float) $printApprovedBudgets->sum('allocated_amount');
    $printRemaining = (float) $printApprovedBudgets->sum('remaining_balance');
    $printUsed = max(0, $printAllocated - $printRemaining);
    $printDepartmentGroups = $budgets
        ->groupBy(fn ($budget) => $budget->department ?: 'Unassigned')
        ->sortKeys();
    $printFilterLabels = [
        'all' => 'All budget types',
        'enrollment' => 'Enrollment fees only',
        'custom' => 'Custom / other budgets',
    ];
    $printSortLabels = [
        'dept_enrollment' => 'Enrollment fees first',
        'amount_desc' => 'Highest amount',
        'title_asc' => 'Title A-Z',
        'latest' => 'Newest first',
    ];
@endphp

<section class="print-budget-report" aria-hidden="true">
    <header class="print-report-header">
        <div class="print-report-brand">
            <img src="{{ asset('assets/images/ssc_logo.png') }}" alt="SSC logo" class="print-report-logo">
            <div>
                <div class="print-report-title">Budget Management Report</div>
                <div class="print-report-subtitle">Supreme Student Council &mdash; Budget allocation and utilization breakdown</div>
            </div>
        </div>
        <div class="print-report-meta">
            <strong>Generated:</strong> {{ now()->format('F d, Y h:i A') }}<br>
            <strong>Prepared by:</strong> {{ Auth::user()->fullname }}
        </div>
    </header>

    <div class="print-filter-line">
        <strong>Report scope:</strong> {{ $printFilterLabels[$filter] ?? 'All budget types' }}
        @if($search)
            &nbsp;&bull;&nbsp; <strong>Search:</strong> “{{ $search }}”
        @endif
        &nbsp;&bull;&nbsp; <strong>Order:</strong> {{ $printSortLabels[$sort] ?? 'Default' }}
        &nbsp;&bull;&nbsp; <strong>Status count:</strong>
        {{ $budgets->where('status', 'Approved')->count() }} approved,
        {{ $budgets->where('status', 'Pending')->count() }} pending,
        {{ $budgets->where('status', 'Rejected')->count() }} rejected
    </div>

    <table class="print-summary-table" aria-label="Budget report totals">
        <tr>
            <td>
                <div class="print-summary-label">Budget Records</div>
                <div class="print-summary-value">{{ $budgets->count() }}</div>
            </td>
            <td>
                <div class="print-summary-label">Approved Allocation</div>
                <div class="print-summary-value">{{ \App\Helpers\SscHelper::formatCurrency($printAllocated) }}</div>
            </td>
            <td>
                <div class="print-summary-label">Funds Used</div>
                <div class="print-summary-value">{{ \App\Helpers\SscHelper::formatCurrency($printUsed) }}</div>
            </td>
            <td>
                <div class="print-summary-label">Remaining Balance</div>
                <div class="print-summary-value">{{ \App\Helpers\SscHelper::formatCurrency($printRemaining) }}</div>
            </td>
        </tr>
    </table>

    <section class="print-section">
        <h2 class="print-section-title">Department Summary</h2>
        <table class="print-table" aria-label="Budget summary by department">
            <thead>
                <tr>
                    <th style="width:24%;">Department</th>
                    <th class="center" style="width:10%;">Records</th>
                    <th class="amount" style="width:20%;">Approved Allocation</th>
                    <th class="amount" style="width:18%;">Funds Used</th>
                    <th class="amount" style="width:20%;">Remaining</th>
                    <th class="center" style="width:8%;">Used</th>
                </tr>
            </thead>
            <tbody>
                @forelse($printDepartmentGroups as $department => $departmentBudgets)
                    @php
                        $departmentApproved = $departmentBudgets->where('status', 'Approved');
                        $departmentAllocated = (float) $departmentApproved->sum('allocated_amount');
                        $departmentRemaining = (float) $departmentApproved->sum('remaining_balance');
                        $departmentUsed = max(0, $departmentAllocated - $departmentRemaining);
                        $departmentUsedPercent = $departmentAllocated > 0
                            ? min(100, round(($departmentUsed / $departmentAllocated) * 100))
                            : 0;
                    @endphp
                    <tr>
                        <td><strong>{{ $department }}</strong></td>
                        <td class="center">{{ $departmentBudgets->count() }}</td>
                        <td class="amount">{{ \App\Helpers\SscHelper::formatCurrency($departmentAllocated) }}</td>
                        <td class="amount">{{ \App\Helpers\SscHelper::formatCurrency($departmentUsed) }}</td>
                        <td class="amount">{{ \App\Helpers\SscHelper::formatCurrency($departmentRemaining) }}</td>
                        <td class="center">{{ $departmentUsedPercent }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="center">No budget records match the selected report scope.</td></tr>
                @endforelse
            </tbody>
            @if($budgets->isNotEmpty())
                <tfoot>
                    <tr>
                        <td><strong>Approved Totals</strong></td>
                        <td class="center">{{ $budgets->count() }}</td>
                        <td class="amount"><strong>{{ \App\Helpers\SscHelper::formatCurrency($printAllocated) }}</strong></td>
                        <td class="amount"><strong>{{ \App\Helpers\SscHelper::formatCurrency($printUsed) }}</strong></td>
                        <td class="amount"><strong>{{ \App\Helpers\SscHelper::formatCurrency($printRemaining) }}</strong></td>
                        <td class="center">{{ $printAllocated > 0 ? min(100, round(($printUsed / $printAllocated) * 100)) : 0 }}%</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </section>

    <section class="print-section">
        <h2 class="print-section-title">Detailed Budget Breakdown</h2>
        <table class="print-table" aria-label="Detailed budget breakdown">
            <thead>
                <tr>
                    <th class="center" style="width:4%;">#</th>
                    <th style="width:24%;">Budget / Notes</th>
                    <th style="width:12%;">Department</th>
                    <th class="center" style="width:10%;">School Year</th>
                    <th class="amount" style="width:14%;">Allocated</th>
                    <th class="amount" style="width:13%;">Used</th>
                    <th class="amount" style="width:14%;">Remaining</th>
                    <th class="center" style="width:9%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($budgets as $index => $budget)
                    @php
                        $budgetUsed = max(0, (float) $budget->allocated_amount - (float) $budget->remaining_balance);
                    @endphp
                    <tr>
                        <td class="center">{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $budget->title }}</strong>
                            @if($budget->notes)
                                <div class="print-budget-note">{{ $budget->notes }}</div>
                            @endif
                        </td>
                        <td>{{ $budget->department ?: 'Unassigned' }}</td>
                        <td class="center">{{ $budget->school_year ?: 'N/A' }}</td>
                        <td class="amount">{{ \App\Helpers\SscHelper::formatCurrency((float) $budget->allocated_amount) }}</td>
                        <td class="amount">{{ \App\Helpers\SscHelper::formatCurrency($budgetUsed) }}<div class="print-budget-note">{{ $budget->used_percent }}%</div></td>
                        <td class="amount">{{ \App\Helpers\SscHelper::formatCurrency((float) $budget->remaining_balance) }}</td>
                        <td class="center"><span class="print-status">{{ $budget->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="center">No budget records match the selected report scope.</td></tr>
                @endforelse
            </tbody>
            @if($budgets->isNotEmpty())
                <tfoot>
                    <tr>
                        <td colspan="4"><strong>Approved Budget Totals</strong></td>
                        <td class="amount"><strong>{{ \App\Helpers\SscHelper::formatCurrency($printAllocated) }}</strong></td>
                        <td class="amount"><strong>{{ \App\Helpers\SscHelper::formatCurrency($printUsed) }}</strong></td>
                        <td class="amount"><strong>{{ \App\Helpers\SscHelper::formatCurrency($printRemaining) }}</strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </section>

    <div class="print-signatures">
        <div class="print-signature">
            {{ Auth::user()->fullname }}
            <small>Prepared by / Administrator</small>
        </div>
        <div class="print-signature">
            &nbsp;
            <small>Reviewed by / Authorized Officer</small>
        </div>
    </div>
</section>

<div class="budget-screen-content">

<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 fw-bold text-dark mb-1">Budget Management</h1>
        <p class="text-muted mb-0">Create, monitor, and manage department enrollment fees and budget allocations</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary" onclick="window.print()"><i class="bi bi-printer me-1"></i> Print Report</button>
        <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#budgetModal"><i class="bi bi-plus-circle me-1"></i> New Budget</button>
    </div>
</div>

{{-- KPI Summary Cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 16px;">
            <div class="card-body p-3.5 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.3rem;">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Total Approved Budget</div>
                    <div class="fs-5 fw-bold text-dark">{!! \App\Helpers\SscHelper::formatCurrency($totalAllocated) !!}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 16px;">
            <div class="card-body p-3.5 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.3rem;">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Enrollment Fees Fund</div>
                    <div class="fs-5 fw-bold text-dark">{!! \App\Helpers\SscHelper::formatCurrency($totalEnrollmentFees) !!}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 16px;">
            <div class="card-body p-3.5 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.3rem;">
                    <i class="bi bi-piggy-bank-fill"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Remaining Balance</div>
                    <div class="fs-5 fw-bold text-dark">{!! \App\Helpers\SscHelper::formatCurrency($totalRemaining) !!}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 16px;">
            <div class="card-body p-3.5 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.3rem;">
                    <i class="bi bi-building"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Departments</div>
                    <div class="fs-5 fw-bold text-dark">{{ $departmentsCount }} Covered</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4 search-card border-0 shadow-sm" style="border-radius: 16px;">
    <div class="card-body-custom p-3">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control-custom form-control border-0 bg-light" placeholder="Search title, department, or school year..." value="{{ $search }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="filter" class="form-select form-control-custom" onchange="this.form.submit()">
                    <option value="all" @selected($filter === 'all')>All Budget Types</option>
                    <option value="enrollment" @selected($filter === 'enrollment')>🎓 Enrollment Fees Only</option>
                    <option value="custom" @selected($filter === 'custom')>📋 Custom / Other Budgets</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="sort" class="form-select form-control-custom" onchange="this.form.submit()">
                    <option value="dept_enrollment" @selected($sort === 'dept_enrollment')>Sort: Enrollment Fees First</option>
                    <option value="amount_desc" @selected($sort === 'amount_desc')>Sort: Highest Amount</option>
                    <option value="title_asc" @selected($sort === 'title_asc')>Sort: Title (A - Z)</option>
                    <option value="latest" @selected($sort === 'latest')>Sort: Newest First</option>
                </select>
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary-custom w-100"><i class="bi bi-filter"></i></button>
                @if($search || $filter !== 'all' || $sort !== 'dept_enrollment')
                    <a href="{{ route('admin.budgets') }}" class="btn btn-outline-secondary" title="Reset Filters"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm" style="border-radius: 16px;">
    <div class="table-responsive-custom">
        <table class="table-custom align-middle">
            <thead>
                <tr>
                    <th>Budget Title</th>
                    <th>Department</th>
                    <th>School Year</th>
                    <th>Allocated Amount</th>
                    <th>Remaining Balance</th>
                    <th>Usage</th>
                    <th>Status</th>
                    <th class="text-end pe-3">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($budgets as $b)
                @php
                    $isEnrollmentFee = ($b->title === \App\Models\Budget::ENROLLMENT_TITLE_PREFIX);
                @endphp
                <tr style="{{ $isEnrollmentFee ? 'background-color: rgba(16, 185, 129, 0.03);' : '' }}">
                    <td>
                        <div class="fw-bold text-dark">{{ $b->title }}</div>
                        @if($isEnrollmentFee)
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 mt-1" style="font-size:0.65rem; font-weight:700;">
                                <i class="bi bi-mortarboard-fill me-1"></i> Consolidated Enrollment Fees
                            </span>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border px-2.5 py-1.5 fw-semibold" style="font-size:0.75rem;">
                            <i class="bi bi-building me-1 text-muted"></i> {{ $b->department }}
                        </span>
                    </td>
                    <td><span class="text-muted small fw-semibold">SY {{ $b->school_year ?? 'N/A' }}</span></td>
                    <td class="fw-bold text-dark">{!! \App\Helpers\SscHelper::formatCurrency($b->allocated_amount) !!}</td>
                    <td class="fw-bold text-success">{!! \App\Helpers\SscHelper::formatCurrency($b->remaining_balance) !!}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div style="background:var(--slate-100);border-radius:6px;height:8px;width:90px;overflow:hidden; flex-shrink:0;">
                                <div class="budget-bar-fill" data-width="{{ $b->used_percent }}" style="height:100%;width:{{ $b->used_percent }}%;background:{{ $b->used_percent > 80 ? 'var(--danger)' : 'var(--primary)' }};border-radius:6px;transition:width 0.6s ease;"></div>
                            </div>
                            <span style="font-size:.72rem;color:var(--slate-500);font-weight:700;">{{ $b->used_percent }}%</span>
                        </div>
                    </td>
                    <td>{!! \App\Helpers\SscHelper::statusBadge($b->status) !!}</td>
                    <td class="text-end pe-3">
                        <div class="d-flex gap-1 justify-content-end flex-wrap">
                            @if($b->status === 'Pending')
                            <form method="POST" action="{{ route('admin.budgets.approve', $b) }}" class="d-inline">@csrf @method('PATCH')
                                <button class="btn btn-success btn-sm px-2 py-1" style="font-size:.72rem;" title="Approve Budget"><i class="bi bi-check2"></i></button>
                            </form>
                            <form method="POST" action="{{ route('admin.budgets.reject', $b) }}" class="d-inline">@csrf @method('PATCH')
                                <button class="btn btn-outline-danger btn-sm px-2 py-1" style="font-size:.72rem;" title="Reject Budget"><i class="bi bi-x"></i></button>
                            </form>
                            @endif
                            <form method="POST" action="{{ route('admin.budgets.destroy', $b) }}" class="d-inline" onsubmit="return confirm('Delete this budget?')">@csrf @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm px-2 py-1" style="font-size:.72rem;" title="Delete Budget"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center py-5 text-muted">No budgets found matching the criteria.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Budget Modal --}}
<div class="modal fade" id="budgetModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:var(--radius);border:none;">
            <div class="modal-header modal-header-custom">
                <h5 class="modal-title" style="font-weight:700;"><i class="bi bi-wallet2"></i> Create New Budget</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.budgets.store') }}" id="budgetForm">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="budgetTitle" class="form-label-custom">Budget Title <span class="text-danger">*</span></label>
                            <input type="text" id="budgetTitle" name="title" value="{{ old('title') }}" class="form-control-custom @error('title') is-invalid @enderror" required>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="budgetDepartment" class="form-label-custom">Department <span class="text-danger">*</span></label>
                            <select id="budgetDepartment" name="department" class="form-select form-control-custom @error('department') is-invalid @enderror" required>
                                <option value="" disabled @selected(!old('department'))>Select a department</option>
                                @foreach($departmentOptions as $department)
                                    <option value="{{ $department }}" @selected(old('department') === $department)>{{ $department }}</option>
                                @endforeach
                            </select>
                            @error('department')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="allocatedAmount" class="form-label-custom">Allocated Amount (₱) <span class="text-danger">*</span></label>
                            <input type="text" id="allocatedAmount" name="allocated_amount" value="{{ old('allocated_amount') }}" class="form-control-custom @error('allocated_amount') is-invalid @enderror" inputmode="decimal" pattern="[1-9][0-9]*(\.[0-9]{1,2})?" placeholder="e.g. 15000.00" aria-describedby="allocatedAmountHelp" required>
                            <div id="allocatedAmountHelp" class="form-text">The amount must begin with 1-9; leading zeroes are not allowed.</div>
                            @error('allocated_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="budgetSchoolYear" class="form-label-custom">School Year <span class="text-danger">*</span></label>
                            <select id="budgetSchoolYear" name="school_year" class="form-select form-control-custom @error('school_year') is-invalid @enderror" required>
                                <option value="" disabled>Select a school year</option>
                                @foreach($schoolYearOptions as $schoolYear)
                                    <option value="{{ $schoolYear }}" @selected(old('school_year', $defaultSchoolYear) === $schoolYear)>{{ $schoolYear }}</option>
                                @endforeach
                            </select>
                            @error('school_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label for="budgetNotes" class="form-label-custom">Notes</label>
                            <textarea id="budgetNotes" name="notes" class="form-control-custom @error('notes') is-invalid @enderror" rows="3" style="resize:vertical;">{{ old('notes') }}</textarea>
                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-primary-custom"><i class="bi bi-check2"></i> Create Budget</button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const amountInput = document.getElementById('allocatedAmount');

    if (amountInput) {
        const validateAmount = function () {
            const hasLeadingZero = /^0/.test(amountInput.value.trim());
            amountInput.setCustomValidity(hasLeadingZero ? 'The allocated amount cannot start with 0.' : '');
        };

        amountInput.addEventListener('input', validateAmount);
        validateAmount();
    }

    @if(isset($errors) && $errors->any())
        bootstrap.Modal.getOrCreateInstance(document.getElementById('budgetModal')).show();
    @endif
});
</script>
@endpush
