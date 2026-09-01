@extends('layouts.app')

@section('sidebar-nav') @include('partials.sidebar-admin') @endsection

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

<div class="page-header">
    <div><h1>Admin Dashboard</h1><p>System overview and financial summary</p></div>
</div>

{{-- Pending Device Login Approvals --}}
@if(!empty($pendingApprovals))
    @foreach($pendingApprovals as $approval)
        <div class="alert alert-warning d-flex align-items-center justify-content-between p-3 mb-3" style="border-radius: 8px; border-left: 5px solid #d97706; background-color: #fef3c7; color: #78350f; box-shadow: 0 4px 12px rgba(217,119,6,0.15); margin-top: -4px;">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-shield-fill-exclamation" style="font-size: 1.8rem; color: #d97706;"></i>
                <div>
                    <h6 class="mb-1" style="font-weight: 700; font-size: 0.9rem;">Security Alert: Login Attempt on New Device</h6>
                    <p class="mb-0" style="font-size: 0.78rem; opacity: 0.9;">
                        An unrecognized device is attempting to log in to your account from IP: <strong style="font-family: monospace;">{{ $approval['ip'] }}</strong> ({{ $approval['user_agent'] }}). Do you authorize this device?
                    </p>
                    @if(!empty($approval['latitude']) && !empty($approval['longitude']))
                        <div class="mt-1 d-flex align-items-center gap-1" style="font-size: 0.75rem;">
                            <i class="bi bi-geo-alt-fill text-danger"></i> Location:
                            <a href="https://www.google.com/maps?q={{ urlencode($approval['latitude'] . ',' . $approval['longitude']) }}" target="_blank" rel="noopener noreferrer" class="badge bg-white text-danger border text-decoration-none shadow-sm d-inline-flex align-items-center gap-1 ms-1 px-2 py-1" style="font-size: 0.72rem; border-color: #fca5a5 !important; color: #b91c1c !important; background-color: #fef2f2 !important;" title="Open in Google Maps">
                                <span>{{ $approval['latitude'] }}, {{ $approval['longitude'] }}</span>
                                <i class="bi bi-box-arrow-up-right" style="font-size: 0.62rem;"></i>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
            <div class="d-flex gap-2">
                <form method="POST" action="{{ route('admin.login_approvals.approve', $approval['id']) }}">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm px-3" style="font-weight: 700; font-size: 0.78rem; border-radius: 4px;">
                        <i class="bi bi-check-circle"></i> Authorize
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.login_approvals.reject', $approval['id']) }}">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm px-3" style="font-weight: 700; font-size: 0.78rem; border-radius: 4px;">
                        <i class="bi bi-x-circle"></i> Block
                    </button>
                </form>
            </div>
        </div>
    @endforeach
@endif

{{-- Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon primary"><i class="bi bi-wallet2"></i></div>
            <div class="stat-info">
                <div class="label">Total Budget</div>
                <div class="value" data-count="{{ $totalBudget }}" data-currency="1">₱0.00</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon danger"><i class="bi bi-arrow-down-circle"></i></div>
            <div class="stat-info">
                <div class="label">Total Expenses</div>
                <div class="value" data-count="{{ $totalExpenses }}" data-currency="1">₱0.00</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon accent"><i class="bi bi-piggy-bank"></i></div>
            <div class="stat-info">
                <div class="label">Remaining</div>
                <div class="value" data-count="{{ $remainingBudget }}" data-currency="1">₱0.00</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon warning"><i class="bi bi-people"></i></div>
            <div class="stat-info">
                <div class="label">Total Users</div>
                <div class="value" data-count="{{ $totalUsers }}">0</div>
            </div>
        </div>
    </div>
</div>

{{-- Pending Summary & Charts Layout --}}
<div class="row g-3" style="flex: 1; min-height: 0;">
    {{-- Left Column: Charts --}}
    <div class="col-lg-5 col-xl-4 d-flex flex-column" style="gap: 12px;">
        <div class="card" style="flex: 1; display: flex; flex-direction: column;">
            <div class="card-header-custom"><span class="card-title">Budget Distribution</span></div>
            <div class="card-body-custom d-flex align-items-center justify-content-center" style="flex: 1; position: relative;">
                <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; align-items: center; justify-content: center; padding: 10px;">
                    <canvas id="budgetPieChart" style="width:100%; height:100%; max-height: 320px;"></canvas>
                </div>
            </div>
        </div>
        <div class="card" style="flex: 1; display: flex; flex-direction: column;">
            <div class="card-header-custom"><span class="card-title">Monthly Expense Trend</span></div>
            <div class="card-body-custom d-flex align-items-center justify-content-center" style="flex: 1; position: relative;">
                <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; align-items: center; justify-content: center; padding: 10px;">
                    <canvas id="expenseBarChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Right Column: Pending Stats + Recent Expenses --}}
    <div class="col-lg-7 col-xl-8 d-flex flex-column" style="gap: 12px;">
        <div class="row g-3">
            <div class="col-md-4"><div class="stat-card"><div class="stat-icon warning"><i class="bi bi-hourglass-split"></i></div><div class="stat-info"><div class="label">Pending Proposals</div><div class="value" data-count="{{ $pendingProposals }}">0</div></div></div></div>
            <div class="col-md-4"><div class="stat-card"><div class="stat-icon secondary"><i class="bi bi-receipt-cutoff"></i></div><div class="stat-info"><div class="label">Pending Expenses</div><div class="value" data-count="{{ $pendingExpenses }}">0</div></div></div></div>
            <div class="col-md-4"><div class="stat-card"><div class="stat-icon primary"><i class="bi bi-chat-dots"></i></div><div class="stat-info"><div class="label">Pending Feedback</div><div class="value" data-count="{{ $pendingFeedback }}">0</div></div></div></div>
        </div>
        
        <div class="card" style="flex: 1; min-height: 0; display: flex; flex-direction: column;">
            <div class="card-header-custom d-flex justify-content-between align-items-center">
                <span class="card-title mb-0">Recent Expenses</span>
                <a href="{{ route('admin.expenses') }}" style="font-size: 0.75rem; text-decoration: none;">View All <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="table-responsive-custom" style="flex: 1; overflow-y: auto;">
                <table class="table-custom">
                    <thead><tr><th>Title</th><th>Officer</th><th>Budget Fund</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
                    <tbody>
                    @forelse($recentExpenses as $ex)
                    <tr>
                        <td style="font-weight:700;">{{ $ex->expense_title }}</td>
                        <td style="font-size:.82rem;">{{ $ex->officer->fullname ?? 'N/A' }}</td>
                        <td><span class="badge bg-primary" style="font-size:.7rem;">{{ $ex->budget->title ?? 'N/A' }}</span></td>
                        <td style="font-weight:700;color:var(--danger);">{!! \App\Helpers\SscHelper::formatCurrency($ex->amount) !!}</td>
                        <td>{!! \App\Helpers\SscHelper::statusBadge($ex->status) !!}</td>
                        <td style="font-size:.78rem;white-space:nowrap;">{{ $ex->created_at->format('M d, Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-4 text-muted">No expenses yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    createPieChart('budgetPieChart',
        {!! json_encode($budgets->pluck('title')) !!},
        {!! json_encode($budgets->pluck('allocated_amount')->map(fn($v) => (float)$v)) !!}
    );
    createBarChart('expenseBarChart',
        {!! json_encode($monthlyExpenses->pluck('month')) !!},
        {!! json_encode($monthlyExpenses->pluck('total')->map(fn($v) => (float)$v)) !!}
    );
});
</script>
@endpush
