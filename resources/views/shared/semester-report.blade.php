{{--
  The per-semester transparency report, shared by the student, treasurer and
  admin portals. Expects $terms, $term, $report and $portal; $sidebar names the
  nav partial for whoever is signed in.
--}}
@extends('layouts.app')

@section('sidebar-nav') @include($sidebar) @endsection

@php
    $money = fn ($amount) => \App\Helpers\SscHelper::formatCurrency((float) $amount);
@endphp

@section('content')
<div class="page-header d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
  <div>
    <h1><i class="bi bi-journals me-2" style="color:#0ea5e9;"></i>Semester Reports</h1>
    <p>
      @if ($term)
        Where every peso went in {{ $term->academic_term }} &mdash; {{ $term->termRangeLabel() }}
      @else
        A full account of collections, allocations and spending for each academic term
      @endif
    </p>
  </div>
  @if ($term)
    <div class="d-flex gap-2 flex-wrap">
      <a href="{{ route($portal . '.semester_reports.records', $term) }}" target="_blank"
         class="btn btn-outline-secondary btn-sm" style="border-radius:8px;">
        <i class="bi bi-table"></i> Records of Expenses
      </a>
      <a href="{{ route($portal . '.semester_reports.print', $term) }}" target="_blank"
         class="btn btn-outline-secondary btn-sm" style="border-radius:8px;">
        <i class="bi bi-printer"></i> Financial Summary
      </a>
    </div>
  @endif
</div>

{{-- Term picker --}}
<div class="card mb-4">
  <div style="padding:16px;">
    @if ($terms->isEmpty())
      <div class="text-center text-muted py-3">
        <i class="bi bi-calendar-x d-block mb-2" style="font-size:1.6rem;"></i>
        No academic terms have been registered yet, so there is nothing to report on.
      </div>
    @else
      <form method="GET" class="row g-2 align-items-end">
        <div class="col-sm-6 col-lg-5">
          <label for="term" class="form-label fw-bold" style="font-size:.78rem;color:#475569;">Academic Term</label>
          <select name="term" id="term" class="form-select form-select-sm" onchange="this.form.submit()">
            @foreach ($terms as $option)
              <option value="{{ $option->id }}" @selected($term && $term->id === $option->id)>
                {{ $option->academic_term }}@if($option->is_active) (Active)@endif
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-sm-3">
          <button type="submit" class="btn btn-primary btn-sm fw-bold w-100" style="border-radius:8px;">
            <i class="bi bi-arrow-repeat me-1"></i> View Report
          </button>
        </div>
        <div class="col-lg-4 text-lg-end">
          <div style="font-size:.75rem;color:#94a3b8;">
            <i class="bi bi-info-circle me-1"></i>
            {{ $terms->count() }} term{{ $terms->count() === 1 ? '' : 's' }} on record
          </div>
        </div>
      </form>
    @endif
  </div>
</div>

@if ($report)
  @unless ($report->start && $report->end)
    <div class="alert alert-warning" style="border-radius:var(--radius-sm);border:none;font-weight:600;">
      <i class="bi bi-exclamation-triangle-fill me-1"></i>
      This term has no start and end date set, so projects, disbursements and cash book entries
      cannot be matched to it. An administrator can set the dates under Settings &rarr; School Year Management.
    </div>
  @endunless

  @if ($report->isEmpty())
    <div class="card">
      <div class="text-center text-muted py-5">
        <i class="bi bi-inbox d-block mb-2" style="font-size:2rem;"></i>
        <div style="font-weight:700;color:#475569;">Nothing recorded for {{ $term->academic_term }} yet</div>
        <div style="font-size:.82rem;">Collections, funds and projects will appear here as they are recorded.</div>
      </div>
    </div>
  @else

  {{-- Headline figures --}}
  <div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3">
      <div class="stat-card">
        <div class="stat-icon" style="background:rgba(14,165,233,.1);color:#0ea5e9;"><i class="bi bi-cash-stack"></i></div>
        <div class="stat-info">
          <div class="label">Contributions Collected</div>
          <div class="value" data-count="{{ $report->contributionsCollected }}" data-currency="1">₱0.00</div>
          <div class="sub" style="font-size:.65rem;color:#64748b;">{{ number_format($report->contributionPayers) }} student(s) paid</div>
        </div>
      </div>
    </div>
    <div class="col-md-6 col-xl-3">
      <div class="stat-card">
        <div class="stat-icon" style="background:rgba(13,43,92,.1);color:#0d2b5c;"><i class="bi bi-wallet2"></i></div>
        <div class="stat-info">
          <div class="label">Funds Allocated</div>
          <div class="value" data-count="{{ $report->totalAllocated }}" data-currency="1">₱0.00</div>
          <div class="sub" style="font-size:.65rem;color:#64748b;">{{ $report->funds->count() }} approved fund(s)</div>
        </div>
      </div>
    </div>
    <div class="col-md-6 col-xl-3">
      <div class="stat-card">
        <div class="stat-icon" style="background:rgba(217,119,6,.1);color:#d97706;"><i class="bi bi-receipt"></i></div>
        <div class="stat-info">
          <div class="label">Total Spent</div>
          <div class="value" data-count="{{ $report->totalSpent }}" data-currency="1">₱0.00</div>
          <div class="sub" style="font-size:.65rem;color:#64748b;">{{ $report->spentPercent() }}% of allocation</div>
        </div>
      </div>
    </div>
    <div class="col-md-6 col-xl-3">
      <div class="stat-card">
        <div class="stat-icon" style="background:rgba(0,168,120,.1);color:#00a878;"><i class="bi bi-piggy-bank"></i></div>
        <div class="stat-info">
          <div class="label">Unspent Balance</div>
          <div class="value" data-count="{{ max(0, $report->totalRemaining) }}" data-currency="1">₱0.00</div>
          <div class="sub" style="font-size:.65rem;color:#64748b;">Still available this term</div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4 mb-4">
    {{-- Funds allocated and drawn down --}}
    <div class="col-lg-7">
      <div class="card h-100">
        <div class="card-header-custom">
          <span class="card-title"><i class="bi bi-wallet2 me-1"></i> Funds for this Semester</span>
        </div>
        <div style="overflow-x:auto;">
          <table class="table table-hover mb-0" style="font-size:.83rem;">
            <thead style="background:#f8fafc;">
              <tr>
                <th style="padding:12px 16px;font-weight:700;color:#475569;">Fund</th>
                <th style="padding:12px 16px;font-weight:700;color:#475569;text-align:right;">Allocated</th>
                <th style="padding:12px 16px;font-weight:700;color:#475569;text-align:right;">Spent</th>
                <th style="padding:12px 16px;font-weight:700;color:#475569;text-align:right;">Remaining</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($report->funds as $fund)
                @php
                  $spent = (float) $report->expenses->where('budget_id', $fund->id)->sum('amount');
                  $allocated = (float) $fund->allocated_amount;
                  $pct = $allocated > 0 ? min(100, (int) round($spent / $allocated * 100)) : 0;
                @endphp
                <tr>
                  <td style="padding:12px 16px;">
                    <div style="font-weight:600;color:#1e293b;">{{ $fund->title }}</div>
                    <div style="font-size:.75rem;color:#94a3b8;">{{ $fund->department ?: '—' }}</div>
                    <div style="height:4px;background:#f1f5f9;border-radius:99px;margin-top:4px;overflow:hidden;">
                      <div style="height:100%;width:{{ $pct }}%;background:{{ $pct >= 100 ? '#ef4444' : '#0ea5e9' }};border-radius:99px;"></div>
                    </div>
                  </td>
                  <td style="padding:12px 16px;text-align:right;font-weight:700;">{{ $money($allocated) }}</td>
                  <td style="padding:12px 16px;text-align:right;font-weight:700;color:#d97706;">{{ $money($spent) }}</td>
                  <td style="padding:12px 16px;text-align:right;font-weight:700;color:#00a878;">{{ $money(max(0, $allocated - $spent)) }}</td>
                </tr>
              @empty
                <tr><td colspan="4" class="text-center text-muted py-4">No approved funds recorded for this term.</td></tr>
              @endforelse
            </tbody>
            @if ($report->funds->isNotEmpty())
              <tfoot>
                <tr style="background:#f8fafc;">
                  <td style="padding:12px 16px;font-weight:800;color:#0f172a;">Total</td>
                  <td style="padding:12px 16px;text-align:right;font-weight:800;">{{ $money($report->totalAllocated) }}</td>
                  <td style="padding:12px 16px;text-align:right;font-weight:800;color:#d97706;">{{ $money($report->totalSpent) }}</td>
                  <td style="padding:12px 16px;text-align:right;font-weight:800;color:#00a878;">{{ $money(max(0, $report->totalRemaining)) }}</td>
                </tr>
              </tfoot>
            @endif
          </table>
        </div>
      </div>
    </div>

    {{-- Cash book movement for the term --}}
    <div class="col-lg-5">
      <div class="card h-100">
        <div class="card-header-custom">
          <span class="card-title"><i class="bi bi-journal-bookmark me-1"></i> Cash Movement</span>
        </div>
        <div style="padding:16px;">
          <div class="d-flex justify-content-between align-items-center mb-2" style="font-size:.85rem;">
            <span style="font-weight:700;color:#1e293b;">Collections Received</span>
            <strong style="color:#00a878;">{{ $money($report->cashCollections) }}</strong>
          </div>
          <div class="d-flex justify-content-between align-items-center mb-3 pb-3" style="font-size:.85rem;border-bottom:1px solid #e2e8f0;">
            <span style="font-weight:700;color:#1e293b;">Cash Expenses Paid</span>
            <strong style="color:#dc2626;">{{ $money($report->cashExpenses) }}</strong>
          </div>

          <div style="font-size:.74rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.04em;margin-bottom:10px;">
            Expenses by Category
          </div>
          @forelse ($report->cashCategoryTotals as $category => $amount)
            @php $pct = $report->cashExpenses > 0 ? (int) round($amount / $report->cashExpenses * 100) : 0; @endphp
            <div style="margin-bottom:14px;">
              <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px;">
                <div style="font-weight:700;font-size:.8rem;color:#1e293b;">{{ $category }}</div>
                <div style="font-size:.8rem;color:#64748b;"><strong style="color:#d97706;">{{ $money($amount) }}</strong></div>
              </div>
              <div style="height:8px;background:#f1f5f9;border-radius:99px;overflow:hidden;">
                <div style="height:100%;width:{{ $pct }}%;background:linear-gradient(90deg,#d97706,#f59e0b);border-radius:99px;"></div>
              </div>
              <div style="font-size:.71rem;color:#94a3b8;margin-top:3px;">{{ $pct }}% of cash expenses</div>
            </div>
          @empty
            <div class="text-center text-muted py-3" style="font-size:.82rem;">No cash expenses recorded in this term.</div>
          @endforelse
        </div>
      </div>
    </div>
  </div>

  {{-- Month by month, matching the printed Records of Expenses --}}
  @if ($report->months->isNotEmpty())
    <div class="card mb-4">
      <div class="card-header-custom d-flex justify-content-between align-items-center">
        <span class="card-title"><i class="bi bi-calendar3 me-1"></i> Monthly Breakdown</span>
        <span class="badge bg-secondary">{{ $report->months->count() }} month(s)</span>
      </div>
      <div style="overflow-x:auto;">
        <table class="table table-hover mb-0" style="font-size:.82rem;">
          <thead style="background:#f8fafc;">
            <tr>
              <th style="padding:12px 16px;font-weight:700;color:#475569;">Month</th>
              <th style="padding:12px 16px;font-weight:700;color:#475569;text-align:right;">Balance Forward</th>
              <th style="padding:12px 16px;font-weight:700;color:#475569;text-align:right;">Collections</th>
              <th style="padding:12px 16px;font-weight:700;color:#475569;text-align:right;">Expenses</th>
              <th style="padding:12px 16px;font-weight:700;color:#475569;text-align:right;">Ending Balance</th>
              <th style="padding:12px 16px;font-weight:700;color:#475569;text-align:center;">Entries</th>
              @if ($portal === 'treasurer')
                <th style="padding:12px 16px;font-weight:700;color:#475569;text-align:center;">Month Sheets</th>
              @endif
            </tr>
          </thead>
          <tbody>
            @foreach ($report->months as $month)
              <tr>
                <td style="padding:10px 16px;font-weight:600;color:#1e293b;white-space:nowrap;">
                  {{ $month->label() }}
                  @if ($month->isPartial())
                    <div style="font-size:.7rem;color:#94a3b8;">{{ $month->coverageLabel() }}</div>
                  @endif
                </td>
                <td style="padding:10px 16px;text-align:right;color:#64748b;">{{ $money($month->beginningBalance) }}</td>
                <td style="padding:10px 16px;text-align:right;font-weight:700;color:#00a878;">{{ $money($month->totalCollections) }}</td>
                <td style="padding:10px 16px;text-align:right;font-weight:700;color:#dc2626;">{{ $money($month->totalExpenses) }}</td>
                <td style="padding:10px 16px;text-align:right;font-weight:700;color:#0f172a;">{{ $money($month->endingBalance()) }}</td>
                <td style="padding:10px 16px;text-align:center;color:#64748b;">{{ $month->entries->count() }}</td>
                @if ($portal === 'treasurer')
                  <td style="padding:10px 16px;text-align:center;white-space:nowrap;">
                    <a href="{{ route('treasurer.cashbook.records', $month->key()) }}" target="_blank"
                       style="color:#3b82f6;font-size:.78rem;text-decoration:none;" title="Records of Expenses for {{ $month->label() }}">
                      <i class="bi bi-table"></i> Records
                    </a>
                    <span style="color:#cbd5e1;">|</span>
                    <a href="{{ route('treasurer.cashbook.financial', $month->key()) }}" target="_blank"
                       style="color:#3b82f6;font-size:.78rem;text-decoration:none;" title="Financial Report for {{ $month->label() }}">
                      <i class="bi bi-file-earmark-text"></i> Financial
                    </a>
                  </td>
                @endif
              </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr style="background:#f8fafc;">
              <td style="padding:12px 16px;font-weight:800;color:#0f172a;">Whole Semester</td>
              <td style="padding:12px 16px;text-align:right;font-weight:800;color:#64748b;">{{ $money($report->openingBalance) }}</td>
              <td style="padding:12px 16px;text-align:right;font-weight:800;color:#00a878;">{{ $money($report->cashCollections) }}</td>
              <td style="padding:12px 16px;text-align:right;font-weight:800;color:#dc2626;">{{ $money($report->cashExpenses) }}</td>
              <td style="padding:12px 16px;text-align:right;font-weight:800;color:#0f172a;">{{ $money($report->closingBalance()) }}</td>
              <td style="padding:12px 16px;"></td>
              @if ($portal === 'treasurer')<td style="padding:12px 16px;"></td>@endif
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  @endif

  {{-- Accountability counters --}}
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="stat-card">
        <div class="stat-icon" style="background:rgba(79,70,229,.1);color:#4f46e5;"><i class="bi bi-lightbulb"></i></div>
        <div class="stat-info">
          <div class="label">Projects Proposed</div>
          <div class="value">{{ number_format($report->projectsProposed) }}</div>
          <div class="sub" style="font-size:.65rem;color:#64748b;">{{ $report->projectsApproved }} approved &bull; {{ $report->projectsCompleted }} completed</div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="stat-card">
        <div class="stat-icon" style="background:rgba(217,119,6,.1);color:#d97706;"><i class="bi bi-cash-coin"></i></div>
        <div class="stat-info">
          <div class="label">Released to Projects</div>
          <div class="value" data-count="{{ $report->totalReleased }}" data-currency="1">₱0.00</div>
          <div class="sub" style="font-size:.65rem;color:#64748b;">{{ $report->releaseCount }} disbursement(s) &bull; {{ $report->releasedPercent() }}% of approved</div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="stat-card">
        <div class="stat-icon" style="background:rgba(16,185,129,.1);color:#10b981;"><i class="bi bi-folder-check"></i></div>
        <div class="stat-info">
          <div class="label">Liquidations Filed</div>
          <div class="value">{{ number_format($report->liquidationsFiled) }}</div>
          <div class="sub" style="font-size:.65rem;color:#64748b;">{{ $report->liquidationsApproved }} approved by the admin</div>
        </div>
      </div>
    </div>
  </div>

  {{-- Projects funded this term --}}
  <div class="card mb-4">
    <div class="card-header-custom d-flex justify-content-between align-items-center">
      <span class="card-title"><i class="bi bi-lightbulb me-1"></i> Projects Proposed this Semester</span>
      <span class="badge bg-secondary">{{ $projects->total() }} records</span>
    </div>
    <div style="overflow-x:auto;">
      <table class="table table-hover mb-0" style="font-size:.82rem;">
        <thead style="background:#f8fafc;">
          <tr>
            <th style="padding:12px 16px;font-weight:700;color:#475569;">Project</th>
            <th style="padding:12px 16px;font-weight:700;color:#475569;">Officer</th>
            <th style="padding:12px 16px;font-weight:700;color:#475569;text-align:right;">Requested</th>
            <th style="padding:12px 16px;font-weight:700;color:#475569;text-align:right;">Approved</th>
            <th style="padding:12px 16px;font-weight:700;color:#475569;text-align:right;">Released</th>
            <th style="padding:12px 16px;font-weight:700;color:#475569;">Filed</th>
            <th style="padding:12px 16px;font-weight:700;color:#475569;text-align:center;">Status</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($projects as $project)
            <tr>
              <td style="padding:10px 16px;font-weight:600;color:#1e293b;max-width:220px;">{{ $project->project_title }}</td>
              <td style="padding:10px 16px;color:#64748b;">{{ $project->officer->fullname ?? '—' }}</td>
              <td style="padding:10px 16px;text-align:right;font-weight:700;">{{ $money($project->requested_budget) }}</td>
              <td style="padding:10px 16px;text-align:right;font-weight:700;color:{{ $project->status === 'Approved' ? '#0d2b5c' : '#cbd5e1' }};">
                {{ $project->status === 'Approved' ? $money($project->approved_budget) : '—' }}
              </td>
              <td style="padding:10px 16px;text-align:right;font-weight:700;color:#d97706;">{{ $money($project->releasedAmount()) }}</td>
              <td style="padding:10px 16px;color:#64748b;white-space:nowrap;">{{ $project->created_at?->format('M d, Y') ?? '—' }}</td>
              <td style="padding:10px 16px;text-align:center;">
                {!! \App\Helpers\SscHelper::statusBadge($project->status) !!}
                @if ($project->project_status === 'Completed')
                  <div style="margin-top:3px;"><span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:.68rem;border-radius:20px;padding:2px 8px;">Completed</span></div>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center text-muted py-4">No projects were proposed during this term.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    {{ $projects->links('partials.pagination') }}
  </div>

  {{-- Expense lines charged to the term's funds --}}
  <div class="card mb-4">
    <div class="card-header-custom d-flex justify-content-between align-items-center">
      <span class="card-title"><i class="bi bi-receipt-cutoff me-1"></i> Approved Expenses Charged to this Semester</span>
      <span class="badge bg-secondary">{{ $expenses->total() }} records</span>
    </div>
    <div style="overflow-x:auto;">
      <table class="table table-hover mb-0" style="font-size:.82rem;">
        <thead style="background:#f8fafc;">
          <tr>
            <th style="padding:12px 16px;font-weight:700;color:#475569;">Expense</th>
            <th style="padding:12px 16px;font-weight:700;color:#475569;">Charged to Fund</th>
            <th style="padding:12px 16px;font-weight:700;color:#475569;">Filed by</th>
            <th style="padding:12px 16px;font-weight:700;color:#475569;text-align:right;">Amount</th>
            <th style="padding:12px 16px;font-weight:700;color:#475569;">Date</th>
            <th style="padding:12px 16px;font-weight:700;color:#475569;">Receipt</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($expenses as $expense)
            <tr>
              <td style="padding:10px 16px;font-weight:600;color:#1e293b;max-width:220px;">{{ $expense->expense_title }}</td>
              <td style="padding:10px 16px;color:#64748b;">{{ $expense->budget->title ?? '—' }}</td>
              <td style="padding:10px 16px;color:#64748b;">{{ $expense->officer->fullname ?? '—' }}</td>
              <td style="padding:10px 16px;text-align:right;font-weight:700;color:#dc2626;">{{ $money($expense->amount) }}</td>
              <td style="padding:10px 16px;color:#64748b;white-space:nowrap;">{{ $expense->created_at?->format('M d, Y') ?? '—' }}</td>
              <td style="padding:10px 16px;">
                @if ($expense->receipt)
                  <button type="button" class="btn btn-outline-primary btn-sm"
                          style="font-size:.72rem;padding:2px 8px;"
                          data-bs-toggle="modal" data-bs-target="#receiptModal{{ $expense->id }}">
                    <i class="bi bi-file-earmark"></i> View
                  </button>
                @else
                  <span style="color:#cbd5e1;">—</span>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center text-muted py-4">No approved expenses charged to this term yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    {{ $expenses->links('partials.pagination') }}
  </div>

  @foreach($expenses->getCollection()->filter->receipt as $ex)
    @include('partials.expense-receipt-modal', [
      'ex' => $ex,
      'showPeople' => true,
    ])
  @endforeach
  @endif
@endif
@endsection
