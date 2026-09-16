@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-admin') @endsection

@section('content')
<style>
@media print {
    body { background-color: white !important; }
    .sidebar, .topbar, .sidebar-overlay, .btn, .modal, .alert, .search-card { display: none !important; }
    .main-wrapper { margin-left: 0 !important; width: 100% !important; padding: 0 !important; }
    .page-content { padding: 0 !important; overflow: visible !important; height: auto !important; }
    .card { box-shadow: none !important; border: none !important; }
    table.table-custom th:last-child, table.table-custom td:last-child { display: none !important; }
    .budget-bar-fill, .badge { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .page-header { margin-bottom: 20px !important; border-bottom: 2px solid #eee; padding-bottom: 10px; }
}
</style>

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
