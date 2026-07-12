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

<div class="page-header d-flex justify-content-between align-items-center">
    <div><h1>Budget Management</h1><p>Create and manage budget allocations</p></div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary" onclick="window.print()"><i class="bi bi-printer"></i> Print Report</button>
        <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#budgetModal"><i class="bi bi-plus-circle"></i> New Budget</button>
    </div>
</div>

<div class="card mb-4 search-card">
    <div class="card-body-custom">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="search" class="form-control-custom flex-fill" placeholder="Search budgets..." value="{{ $search }}">
            <button type="submit" class="btn-primary-custom"><i class="bi bi-search"></i></button>
            @if($search)<a href="{{ route('admin.budgets') }}" class="btn btn-outline-secondary">Clear</a>@endif
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive-custom">
        <table class="table-custom">
            <thead><tr><th>Title</th><th>Department</th><th>Allocated</th><th>Remaining</th><th>Usage</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($budgets as $b)
            <tr>
                <td style="font-weight:700;">{{ $b->title }}</td>
                <td>{{ $b->department }}</td>
                <td>{!! \App\Helpers\SscHelper::formatCurrency($b->allocated_amount) !!}</td>
                <td>{!! \App\Helpers\SscHelper::formatCurrency($b->remaining_balance) !!}</td>
                <td>
                    <div style="background:var(--slate-100);border-radius:6px;height:8px;width:100px;overflow:hidden;">
                        <div class="budget-bar-fill" data-width="{{ $b->used_percent }}" style="height:100%;background:{{ $b->used_percent > 80 ? 'var(--danger)' : 'var(--primary)' }};border-radius:6px;transition:width 1s ease;"></div>
                    </div>
                    <span style="font-size:.72rem;color:var(--slate-500);">{{ $b->used_percent }}%</span>
                </td>
                <td>{!! \App\Helpers\SscHelper::statusBadge($b->status) !!}</td>
                <td>
                    <div class="d-flex gap-1 flex-wrap">
                        @if($b->status === 'Pending')
                        <form method="POST" action="{{ route('admin.budgets.approve', $b) }}" class="d-inline">@csrf @method('PATCH')
                            <button class="btn btn-success btn-sm" style="font-size:.72rem;"><i class="bi bi-check2"></i></button>
                        </form>
                        <form method="POST" action="{{ route('admin.budgets.reject', $b) }}" class="d-inline">@csrf @method('PATCH')
                            <button class="btn btn-outline-danger btn-sm" style="font-size:.72rem;"><i class="bi bi-x"></i></button>
                        </form>
                        @endif
                        <form method="POST" action="{{ route('admin.budgets.destroy', $b) }}" class="d-inline" onsubmit="return confirm('Delete this budget?')">@csrf @method('DELETE')
                            <button class="btn btn-outline-danger btn-sm" style="font-size:.72rem;"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center py-4 text-muted">No budgets found.</td></tr>
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
            <form method="POST" action="{{ route('admin.budgets.store') }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label-custom">Budget Title <span class="text-danger">*</span></label><input type="text" name="title" class="form-control-custom" required></div>
                        <div class="col-md-6"><label class="form-label-custom">Department <span class="text-danger">*</span></label><input type="text" name="department" class="form-control-custom" required></div>
                        <div class="col-md-6"><label class="form-label-custom">Allocated Amount (₱) <span class="text-danger">*</span></label><input type="number" name="allocated_amount" class="form-control-custom" min="1" step="0.01" required></div>
                        <div class="col-md-6"><label class="form-label-custom">School Year <span class="text-danger">*</span></label><input type="text" name="school_year" class="form-control-custom" placeholder="e.g. 2025-2026" required></div>
                        <div class="col-12"><label class="form-label-custom">Notes</label><textarea name="notes" class="form-control-custom" rows="3" style="resize:vertical;"></textarea></div>
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
