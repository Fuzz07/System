@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-officer') @endsection

@section('content')
<style>
    body { overflow: hidden; }
    .page-content {
        height: calc(100vh - 70px);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        padding: 12px 24px !important;
    }
    .page-header {
        margin-bottom: 12px !important;
    }
    .page-header h1 {
        font-size: 1.25rem !important;
        margin-bottom: 2px !important;
    }
    .page-header p {
        font-size: 0.8rem !important;
        margin-bottom: 0 !important;
    }
    .stat-card {
        padding: 12px 16px !important;
        gap: 12px !important;
    }
    .stat-icon {
        width: 40px !important;
        height: 40px !important;
        font-size: 1.2rem !important;
        border-radius: 10px !important;
    }
    .stat-info .value {
        font-size: 1.2rem !important;
    }
    .stat-info .label {
        font-size: 0.7rem !important;
        margin-bottom: 0 !important;
    }
    .card-body-custom {
        padding: 8px !important;
    }
    .card-header-custom {
        padding: 10px 16px !important;
    }
    .card-title {
        font-size: 0.9rem !important;
    }
    .row.g-3, .row.g-4 {
        margin-bottom: 12px !important;
        --bs-gutter-y: 12px;
    }
    .mb-4 {
        margin-bottom: 12px !important;
    }
    .table-custom td, .table-custom th {
        padding: 6px 12px !important;
        font-size: 0.75rem !important;
    }
    .recent-expenses-wrapper {
        flex: 1;
        min-height: 0;
        display: flex;
        flex-direction: column;
        margin-bottom: 0 !important;
    }
    .recent-expenses-wrapper .card {
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .recent-expenses-wrapper .table-responsive-custom {
        flex: 1;
        overflow-y: auto;
    }
</style>
<div class="page-header"><div><h1>Officer Dashboard</h1><p>Your proposals, expenses, and activity overview</p></div></div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3"><div class="stat-card"><div class="stat-icon primary"><i class="bi bi-file-earmark-text"></i></div><div class="stat-info"><div class="label">My Proposals</div><div class="value" data-count="{{ $myProposals }}">0</div></div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="stat-card"><div class="stat-icon danger"><i class="bi bi-receipt"></i></div><div class="stat-info"><div class="label">My Expenses</div><div class="value" data-count="{{ $myExpenses }}">0</div></div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="stat-card"><div class="stat-icon accent"><i class="bi bi-wallet2"></i></div><div class="stat-info"><div class="label">Approved Budget</div><div class="value" data-count="{{ $approvedBudget }}" data-currency="1">₱0.00</div></div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="stat-card"><div class="stat-icon warning"><i class="bi bi-hourglass-split"></i></div><div class="stat-info"><div class="label">Pending Items</div><div class="value" data-count="{{ $pendingItems }}">0</div></div></div></div>
</div>

<div class="recent-expenses-wrapper">
<div class="card">
    <div class="card-header-custom"><span class="card-title">Recent Expenses</span></div>
    <div class="table-responsive-custom"><table class="table-custom">
        <thead><tr><th>Expense</th><th>Budget Fund</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
        <tbody>
        @forelse($recentExpenses as $ex)
        <tr>
            <td style="font-weight:700;">{{ $ex->expense_title }}</td>
            <td><span class="badge bg-primary" style="font-size:.7rem;">{{ $ex->budget->title ?? 'N/A' }}</span></td>
            <td style="font-weight:700;color:var(--danger);">{!! \App\Helpers\SscHelper::formatCurrency($ex->amount) !!}</td>
            <td>{!! \App\Helpers\SscHelper::statusBadge($ex->status) !!}</td>
            <td style="font-size:.78rem;">{{ $ex->created_at?->format('M d, Y') }}</td>
        </tr>
        @empty
        <tr><td colspan="5" class="text-center py-4 text-muted">No expenses yet.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div>
</div>
@endsection
