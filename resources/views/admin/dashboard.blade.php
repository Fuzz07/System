@extends('layouts.app')

@section('sidebar-nav') @include('partials.sidebar-admin') @endsection

@section('content')
    <style>
        body {
            overflow: hidden;
        }

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

        .row.g-3,
        .row.g-4 {
            margin-bottom: 12px !important;
            --bs-gutter-y: 12px;
        }

        .mb-4 {
            margin-bottom: 12px !important;
        }

        .table-custom td,
        .table-custom th {
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

        /* Charts open full size in a pop-up when clicked. */
        .chart-clickable {
            cursor: zoom-in;
            transition: background 0.15s ease;
        }

        .chart-clickable:hover {
            background: var(--slate-50, #f8fafc);
        }

        .chart-expand-btn {
            width: 32px;
            height: 32px;
            display: inline-grid;
            place-items: center;
            color: var(--slate-500, #64748b);
            background: var(--slate-50, #f8fafc);
            border: 1px solid var(--slate-200, #e2e8f0);
            border-radius: 8px;
            transition: color 0.15s ease, background 0.15s ease, border-color 0.15s ease;
        }

        .chart-expand-btn:hover,
        .chart-expand-btn:focus-visible {
            color: var(--primary, #e34f26);
            background: #fff1ec;
            border-color: #fbc8b5;
        }

        .chart-modal-canvas {
            position: relative;
            height: 360px;
        }

        .chart-stat {
            background: var(--slate-50, #f8fafc);
            border: 1px solid var(--slate-200, #e2e8f0);
            border-radius: 12px;
            padding: 10px 12px;
            height: 100%;
        }

        .chart-stat .chart-stat-label {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--slate-500, #64748b);
        }

        .chart-stat .chart-stat-value {
            font-size: 1rem;
            font-weight: 800;
            color: var(--slate-900, #0f172a);
        }

        .chart-swatch {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 3px;
            margin-right: 8px;
            flex-shrink: 0;
        }

        .chart-breakdown {
            max-height: 300px;
            overflow-y: auto;
        }

        .chart-breakdown table {
            font-size: 0.82rem;
        }

        .chart-breakdown thead th {
            position: sticky;
            top: 0;
            background: #fff;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--slate-500, #64748b);
        }
    </style>

    <div class="page-header">
        <div>
            <h1>Admin Dashboard</h1>
            <p>System overview and financial summary</p>
        </div>
    </div>

    {{-- Pending Device Login Approvals --}}
    @if(!empty($pendingApprovals))
        @foreach($pendingApprovals as $approval)
            <div class="alert alert-warning d-flex align-items-center justify-content-between p-3 mb-3"
                style="border-radius: 8px; border-left: 5px solid #d97706; background-color: #fef3c7; color: #78350f; box-shadow: 0 4px 12px rgba(217,119,6,0.15); margin-top: -4px;">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-shield-fill-exclamation" style="font-size: 1.8rem; color: #d97706;"></i>
                    <div>
                        <h6 class="mb-1" style="font-weight: 700; font-size: 0.9rem;">Security Alert: Login Attempt on New Device
                        </h6>
                        <p class="mb-0" style="font-size: 0.78rem; opacity: 0.9;">
                            An unrecognized device is attempting to log in to your account from IP: <strong
                                style="font-family: monospace;">{{ $approval['ip'] }}</strong> ({{ $approval['user_agent'] }}). Do
                            you authorize this device?
                        </p>
                        @if(!empty($approval['latitude']) && !empty($approval['longitude']))
                            <div class="mt-1 d-flex align-items-center gap-1" style="font-size: 0.75rem;">
                                <i class="bi bi-geo-alt-fill text-danger"></i> Location:
                                <a href="https://www.google.com/maps?q={{ urlencode($approval['latitude'] . ',' . $approval['longitude']) }}"
                                    target="_blank" rel="noopener noreferrer"
                                    class="badge bg-white text-danger border text-decoration-none shadow-sm d-inline-flex align-items-center gap-1 ms-1 px-2 py-1"
                                    style="font-size: 0.72rem; border-color: #fca5a5 !important; color: #b91c1c !important; background-color: #fef2f2 !important;"
                                    title="Open in Google Maps">
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
                        <button type="submit" class="btn btn-success btn-sm px-3"
                            style="font-weight: 700; font-size: 0.78rem; border-radius: 4px;">
                            <i class="bi bi-check-circle"></i> Authorize
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.login_approvals.reject', $approval['id']) }}">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm px-3"
                            style="font-weight: 700; font-size: 0.78rem; border-radius: 4px;">
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
            {{-- Clicking either chart opens it full size with its numbers beside it. --}}
            <div class="card" style="flex: 1; display: flex; flex-direction: column;">
                <div class="card-header-custom">
                    <span class="card-title">Budget Distribution</span>
                    <button type="button" class="chart-expand-btn" data-bs-toggle="modal" data-bs-target="#budgetChartModal"
                        title="Expand" aria-label="Expand the budget distribution chart"><i class="bi bi-arrows-angle-expand"></i></button>
                </div>
                <div class="card-body-custom d-flex align-items-center justify-content-center chart-clickable"
                    style="flex: 1; position: relative;" data-bs-toggle="modal" data-bs-target="#budgetChartModal"
                    title="Click to see the full breakdown">
                    <div
                        style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; align-items: center; justify-content: center; padding: 10px;">
                        <canvas id="budgetPieChart" style="width:100%; height:100%; max-height: 320px;"></canvas>
                    </div>
                </div>
            </div>
            <div class="card" style="flex: 1; display: flex; flex-direction: column;">
                <div class="card-header-custom">
                    <span class="card-title">Monthly Expense Trend</span>
                    <button type="button" class="chart-expand-btn" data-bs-toggle="modal" data-bs-target="#expenseChartModal"
                        title="Expand" aria-label="Expand the monthly expense trend chart"><i class="bi bi-arrows-angle-expand"></i></button>
                </div>
                <div class="card-body-custom d-flex align-items-center justify-content-center chart-clickable"
                    style="flex: 1; position: relative;" data-bs-toggle="modal" data-bs-target="#expenseChartModal"
                    title="Click to see the full breakdown">
                    <div
                        style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; align-items: center; justify-content: center; padding: 10px;">
                        <canvas id="expenseBarChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Pending Stats + Recent Expenses --}}
        <div class="col-lg-7 col-xl-8 d-flex flex-column" style="gap: 12px;">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon warning"><i class="bi bi-hourglass-split"></i></div>
                        <div class="stat-info">
                            <div class="label">Pending Proposals</div>
                            <div class="value" data-count="{{ $pendingProposals }}">0</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon secondary"><i class="bi bi-receipt-cutoff"></i></div>
                        <div class="stat-info">
                            <div class="label">Pending Expenses</div>
                            <div class="value" data-count="{{ $pendingExpenses }}">0</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon primary"><i class="bi bi-chat-dots"></i></div>
                        <div class="stat-info">
                            <div class="label">Pending Feedback</div>
                            <div class="value" data-count="{{ $pendingFeedback }}">0</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card" style="flex: 1; min-height: 0; display: flex; flex-direction: column;">
                <div class="card-header-custom d-flex justify-content-between align-items-center">
                    <span class="card-title mb-0">Recent Expenses</span>
                    <a href="{{ route('admin.expenses') }}" style="font-size: 0.75rem; text-decoration: none;">View All <i
                            class="bi bi-arrow-right"></i></a>
                </div>
                <div class="table-responsive-custom" style="flex: 1; overflow-y: auto;">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Officer</th>
                                <th>Budget Fund</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentExpenses as $ex)
                                <tr>
                                    <td style="font-weight:700;">{{ $ex->expense_title }}</td>
                                    <td style="font-size:.82rem;">{{ $ex->officer->fullname ?? 'N/A' }}</td>
                                    <td><span class="badge bg-primary"
                                            style="font-size:.7rem;">{{ $ex->budget->title ?? 'N/A' }}</span></td>
                                    <td style="font-weight:700;color:var(--danger);">
                                        {!! \App\Helpers\SscHelper::formatCurrency($ex->amount) !!}</td>
                                    <td>{!! \App\Helpers\SscHelper::statusBadge($ex->status) !!}</td>
                                    <td style="font-size:.78rem;white-space:nowrap;">{{ $ex->created_at->format('M d, Y') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No expenses yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Chart pop-ups: each chart full size, with the numbers behind it. --}}
    @php
        $budgetAllocated = (float) $budgets->sum('allocated_amount');
        $budgetRemaining = (float) $budgets->sum('remaining_balance');
        $monthLabels = $monthlyExpenses
            ->map(fn ($m) => \Illuminate\Support\Carbon::parse($m->month . '-01')->format('M Y'))
            ->values();
        $monthTotal = (float) $monthlyExpenses->sum('total');
        $peakMonthIndex = $monthlyExpenses->search(fn ($m) => (float) $m->total === (float) $monthlyExpenses->max('total'));
    @endphp

    <div class="modal fade" id="budgetChartModal" tabindex="-1" aria-labelledby="budgetChartModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
            <div class="modal-content" style="border-radius:var(--radius);border:none;overflow:hidden;">
                <div class="modal-header modal-header-custom">
                    <div>
                        <h5 class="modal-title" id="budgetChartModalTitle" style="font-weight:700;"><i class="bi bi-pie-chart-fill"></i> Budget Distribution</h5>
                        <div class="small" style="color: rgba(255,255,255,.7);">How the approved budget is allocated across funds</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    @if($budgets->isEmpty())
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-pie-chart" style="font-size:2rem;opacity:.4;"></i>
                            <div class="mt-2">No approved budgets yet.</div>
                        </div>
                    @else
                        <div class="row g-4 align-items-start">
                            <div class="col-lg-6">
                                <div class="chart-modal-canvas"><canvas id="budgetPieChartLarge" role="img" aria-label="Budget distribution chart"></canvas></div>
                            </div>
                            <div class="col-lg-6">
                                <div class="row g-2 mb-3">
                                    <div class="col-4"><div class="chart-stat"><div class="chart-stat-label">Allocated</div><div class="chart-stat-value">{!! \App\Helpers\SscHelper::formatCurrency($budgetAllocated) !!}</div></div></div>
                                    <div class="col-4"><div class="chart-stat"><div class="chart-stat-label">Remaining</div><div class="chart-stat-value text-success">{!! \App\Helpers\SscHelper::formatCurrency($budgetRemaining) !!}</div></div></div>
                                    <div class="col-4"><div class="chart-stat"><div class="chart-stat-label">Funds</div><div class="chart-stat-value">{{ $budgets->count() }}</div></div></div>
                                </div>
                                <div class="chart-breakdown">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead><tr><th>Fund</th><th class="text-end">Allocated</th><th class="text-end">Share</th></tr></thead>
                                        <tbody>
                                            @foreach($budgets as $i => $b)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-start">
                                                            <span class="chart-swatch mt-1" data-chart-swatch="budget" data-index="{{ $i }}"></span>
                                                            <div>
                                                                <div class="fw-semibold text-dark">{{ $b->title }}</div>
                                                                <div class="text-muted" style="font-size:.72rem;">{{ $b->department }} &middot; SY {{ $b->school_year ?? 'N/A' }} &middot; {!! \App\Helpers\SscHelper::formatCurrency((float) $b->remaining_balance) !!} left</div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-end fw-semibold">{!! \App\Helpers\SscHelper::formatCurrency((float) $b->allocated_amount) !!}</td>
                                                    <td class="text-end text-muted">{{ $budgetAllocated > 0 ? number_format((float) $b->allocated_amount / $budgetAllocated * 100, 1) : '0.0' }}%</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer border-0 pt-0">
                    <a href="{{ route('admin.budgets') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-wallet2"></i> Open Budget Management</a>
                    <button type="button" class="btn btn-outline-secondary btn-sm px-4" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="expenseChartModal" tabindex="-1" aria-labelledby="expenseChartModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
            <div class="modal-content" style="border-radius:var(--radius);border:none;overflow:hidden;">
                <div class="modal-header modal-header-custom">
                    <div>
                        <h5 class="modal-title" id="expenseChartModalTitle" style="font-weight:700;"><i class="bi bi-bar-chart-fill"></i> Monthly Expense Trend</h5>
                        <div class="small" style="color: rgba(255,255,255,.7);">Approved expenses per month, latest {{ $monthlyExpenses->count() === 1 ? 'month' : $monthlyExpenses->count() . ' months' }}</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    @if($monthlyExpenses->isEmpty())
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-bar-chart" style="font-size:2rem;opacity:.4;"></i>
                            <div class="mt-2">No approved expenses yet.</div>
                        </div>
                    @else
                        <div class="row g-4 align-items-start">
                            <div class="col-lg-7">
                                <div class="chart-modal-canvas"><canvas id="expenseBarChartLarge" role="img" aria-label="Monthly expense trend chart"></canvas></div>
                            </div>
                            <div class="col-lg-5">
                                <div class="row g-2 mb-3">
                                    <div class="col-4"><div class="chart-stat"><div class="chart-stat-label">Total</div><div class="chart-stat-value text-danger">{!! \App\Helpers\SscHelper::formatCurrency($monthTotal) !!}</div></div></div>
                                    <div class="col-4"><div class="chart-stat"><div class="chart-stat-label">Monthly Avg</div><div class="chart-stat-value">{!! \App\Helpers\SscHelper::formatCurrency($monthTotal / max(1, $monthlyExpenses->count())) !!}</div></div></div>
                                    <div class="col-4"><div class="chart-stat"><div class="chart-stat-label">Highest</div><div class="chart-stat-value">{{ $monthLabels[$peakMonthIndex] ?? '—' }}</div></div></div>
                                </div>
                                <div class="chart-breakdown">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead><tr><th>Month</th><th class="text-end">Approved</th><th class="text-end">vs Previous</th></tr></thead>
                                        <tbody>
                                            @foreach($monthlyExpenses as $i => $m)
                                                @php
                                                    $previous = $i > 0 ? (float) $monthlyExpenses[$i - 1]->total : null;
                                                    $change = $previous ? ((float) $m->total - $previous) / $previous * 100 : null;
                                                @endphp
                                                <tr>
                                                    <td><div class="d-flex align-items-center"><span class="chart-swatch" data-chart-swatch="month" data-index="{{ $i }}"></span><span class="fw-semibold text-dark">{{ $monthLabels[$i] }}</span></div></td>
                                                    <td class="text-end fw-semibold">{!! \App\Helpers\SscHelper::formatCurrency((float) $m->total) !!}</td>
                                                    <td class="text-end text-muted">
                                                        @if($change === null)
                                                            —
                                                        @else
                                                            <i class="bi {{ $change >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i> {{ number_format(abs($change), 1) }}%
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer border-0 pt-0">
                    <a href="{{ route('admin.expenses') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-receipt-cutoff"></i> Open Expenses</a>
                    <button type="button" class="btn btn-outline-secondary btn-sm px-4" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const budgetLabels = @json($budgets->pluck('title'));
            const budgetData = @json($budgets->pluck('allocated_amount')->map(fn ($v) => (float) $v));
            const monthLabels = @json($monthLabels);
            const monthData = @json($monthlyExpenses->pluck('total')->map(fn ($v) => (float) $v));

            // Clicking a small chart opens its pop-up, so a click on the
            // doughnut's legend should not also hide a slice behind it.
            const budgetChart = createPieChart('budgetPieChart', budgetLabels, budgetData);
            if (budgetChart) {
                budgetChart.options.plugins.legend.onClick = null;
                budgetChart.update('none');
            }
            createBarChart('expenseBarChart', monthLabels, monthData);

            // The full-size charts are drawn the first time their pop-up opens:
            // a chart drawn inside a hidden pop-up has no size to fill.
            const drawOnShow = function (modalId, draw) {
                const modal = document.getElementById(modalId);
                if (!modal) return;
                let drawn = false;
                modal.addEventListener('shown.bs.modal', function () {
                    if (drawn) return;
                    drawn = true;
                    draw();
                });
            };
            drawOnShow('budgetChartModal', () => createPieChart('budgetPieChartLarge', budgetLabels, budgetData));
            drawOnShow('expenseChartModal', () => createBarChart('expenseBarChartLarge', monthLabels, monthData));

            // Colour each table row's swatch like its slice or bar. Repainted
            // after a live refresh, which resets the inline colours.
            const paintSwatches = function () {
                const paint = (kind, colors) => document.querySelectorAll('[data-chart-swatch="' + kind + '"]')
                    .forEach(swatch => { swatch.style.background = colors[swatch.dataset.index] || '#cbd5e1'; });
                paint('budget', generateChartColors(budgetData.length));
                paint('month', ordinalRampColors(monthData.length));
            };
            paintSwatches();
            document.addEventListener('ssc:content-updated', paintSwatches);
        });
    </script>
@endpush