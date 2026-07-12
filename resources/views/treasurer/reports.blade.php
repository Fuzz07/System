@extends('layouts.app')

@section('sidebar-nav') @include('partials.sidebar-treasurer') @endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
  <div>
    <h1><i class="bi bi-bar-chart-line me-2" style="color:#d97706;"></i>Release Reports</h1>
    <p>Audit trail of all budget disbursements — School Year {{ $sy }}</p>
  </div>
  <div>
    <button onclick="window.print()" class="btn btn-outline-secondary btn-sm" style="border-radius: 8px;">
      <i class="bi bi-printer"></i> Print Report
    </button>
  </div>
</div>

<!-- Summary Stats -->
<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="stat-card">
      <div class="stat-icon" style="background: rgba(13, 43, 92, 0.1); color: #0d2b5c;"><i class="bi bi-wallet2"></i></div>
      <div class="stat-info">
        <div class="label">Total Approved Budget</div>
        <div class="value" data-count="{{ $approvedBudget }}" data-currency="1">₱0.00</div>
        <div class="sub" style="font-size: 0.65rem; color: #64748b;">All approved proposals</div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="stat-card">
      <div class="stat-icon" style="background: rgba(217, 119, 6, 0.1); color: #d97706;"><i class="bi bi-cash-coin"></i></div>
      <div class="stat-info">
        <div class="label">Total Disbursed</div>
        <div class="value" data-count="{{ $totalReleased }}" data-currency="1">₱0.00</div>
        <div class="sub" style="font-size: 0.65rem; color: #64748b;">{{ $countReleases }} disbursement(s)</div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="stat-card">
      <div class="stat-icon" style="background: rgba(0, 168, 120, 0.1); color: #00a878;"><i class="bi bi-piggy-bank"></i></div>
      <div class="stat-info">
        <div class="label">Unreleased Balance</div>
        <div class="value" data-count="{{ max(0, $approvedBudget - $totalReleased) }}" data-currency="1">₱0.00</div>
        <div class="sub" style="font-size: 0.65rem; color: #64748b;">Remaining to disburse</div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4 mb-4">
  <!-- Per-Proposal Summary -->
  <div class="col-lg-7">
    <div class="card h-100">
      <div class="card-header-custom">
        <span class="card-title">Per-Project Disbursement Summary</span>
      </div>
      <div style="overflow-x:auto;">
        <table class="table table-hover mb-0" style="font-size:.83rem;">
          <thead style="background:#f8fafc;">
            <tr>
              <th style="padding:12px 16px; font-weight:700; color:#475569;">Project</th>
              <th style="padding:12px 16px; font-weight:700; color:#475569; text-align:right;">Approved</th>
              <th style="padding:12px 16px; font-weight:700; color:#475569; text-align:right;">Released</th>
              <th style="padding:12px 16px; font-weight:700; color:#475569; text-align:right;">Balance</th>
              <th style="padding:12px 16px; font-weight:700; color:#475569; text-align:center;">Status</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($proposalSummary as $ps)
              @php
                $bal = $ps->approved_budget - $ps->total_released;
                $pct = $ps->approved_budget > 0 ? min(100, round(($ps->total_released / $ps->approved_budget) * 100)) : 0;
                $fullyReleased = $bal <= 0.01;
              @endphp
              <tr>
                <td style="padding:12px 16px;">
                  <div style="font-weight:600; color:#1e293b;">{{ $ps->project_title }}</div>
                  <div style="font-size:.75rem; color:#94a3b8;">{{ $ps->officer->fullname ?? '—' }}</div>
                  <div style="height:4px; background:#f1f5f9; border-radius:99px; margin-top:4px; overflow:hidden;">
                    <div style="height:100%; width:{{ $pct }}%; background:{{ $fullyReleased ? '#22c55e' : '#f59e0b' }}; border-radius:99px;"></div>
                  </div>
                </td>
                <td style="padding:12px 16px; text-align:right; font-weight:700;">{!! \App\Helpers\SscHelper::formatCurrency($ps->approved_budget) !!}</td>
                <td style="padding:12px 16px; text-align:right; font-weight:700; color:#d97706;">{!! \App\Helpers\SscHelper::formatCurrency($ps->total_released) !!}</td>
                <td style="padding:12px 16px; text-align:right; font-weight:700; color:{{ $fullyReleased ? '#22c55e' : '#1e293b' }};">
                  {{ $fullyReleased ? '—' : \App\Helpers\SscHelper::formatCurrency($bal) }}
                </td>
                <td style="padding:12px 16px; text-align:center;">
                  @if ($fullyReleased)
                    <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:.72rem; border-radius:20px; padding:3px 10px;">✅ Fully Released</span>
                  @elseif ($ps->total_released > 0)
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size:.72rem; border-radius:20px; padding:3px 10px;">🔄 Partial</span>
                  @else
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size:.72rem; border-radius:20px; padding:3px 10px;">⏳ Pending</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr><td colspan="5" class="text-center text-muted py-4">No approved proposals found.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- By Method -->
  <div class="col-lg-5">
    <div class="card h-100">
      <div class="card-header-custom">
        <span class="card-title">By Release Method</span>
      </div>
      <div style="padding:16px;">
        @forelse ($byMethod as $bm)
          @php $pct = $totalReleased > 0 ? round(($bm->total / $totalReleased) * 100) : 0; @endphp
          <div style="margin-bottom:16px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
              <div style="font-weight:700;font-size:.85rem;color:#1e293b;">{{ $bm->release_method }}</div>
              <div style="font-size:.82rem;color:#64748b;">{{ $bm->cnt }} txn &bull; <strong style="color:#d97706;">{!! \App\Helpers\SscHelper::formatCurrency($bm->total) !!}</strong></div>
            </div>
            <div style="height:8px;background:#f1f5f9;border-radius:99px;overflow:hidden;">
              <div style="height:100%;width:{{ $pct }}%;background:linear-gradient(90deg,#d97706,#f59e0b);border-radius:99px;"></div>
            </div>
            <div style="font-size:.72rem;color:#94a3b8;margin-top:3px;">{{ $pct }}% of total disbursements</div>
          </div>
        @empty
          <div class="text-center text-muted py-4">No disbursements yet.</div>
        @endforelse
      </div>
    </div>
  </div>
</div>

<!-- Full Audit Log -->
<div class="card mb-4">
  <div class="card-header-custom d-flex justify-content-between align-items-center">
    <span class="card-title"><i class="bi bi-journal-text me-1"></i> Full Disbursement Audit Log</span>
    <span class="badge bg-secondary">{{ count($releases) }} records</span>
  </div>
  <div style="overflow-x:auto;">
    <table class="table table-hover mb-0" style="font-size:.82rem;">
      <thead style="background:#f8fafc;">
        <tr>
          <th style="padding:12px 16px; font-weight:700; color:#475569;">#</th>
          <th style="padding:12px 16px; font-weight:700; color:#475569;">Project</th>
          <th style="padding:12px 16px; font-weight:700; color:#475569;">Officer</th>
          <th style="padding:12px 16px; font-weight:700; color:#475569; text-align:right;">Amount</th>
          <th style="padding:12px 16px; font-weight:700; color:#475569;">Method</th>
          <th style="padding:12px 16px; font-weight:700; color:#475569;">Reference</th>
          <th style="padding:12px 16px; font-weight:700; color:#475569;">Released By</th>
          <th style="padding:12px 16px; font-weight:700; color:#475569;">Date</th>
          <th style="padding:12px 16px; font-weight:700; color:#475569; text-align:center;">Status</th>
          <th style="padding:12px 16px; font-weight:700; color:#475569;">Receipt</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($releases as $i => $br)
          <tr>
            <td style="padding:10px 16px; color:#94a3b8;">{{ $i + 1 }}</td>
            <td style="padding:10px 16px; font-weight:600; color:#1e293b; max-width:200px;">
              {{ $br->proposal->project_title ?? 'Unknown' }}
            </td>
            <td style="padding:10px 16px; color:#64748b;">{{ $br->proposal->officer->fullname ?? '—' }}</td>
            <td style="padding:10px 16px; text-align:right; font-weight:700; color:#059669;">{!! \App\Helpers\SscHelper::formatCurrency($br->amount_released) !!}</td>
            <td style="padding:10px 16px;">{{ $br->release_method }}</td>
            <td style="padding:10px 16px; color:#64748b; font-family:monospace; font-size:.78rem;">{{ $br->reference_no ? $br->reference_no : '—' }}</td>
            <td style="padding:10px 16px; color:#64748b;">{{ $br->treasurer->fullname ?? '—' }}</td>
            <td style="padding:10px 16px; color:#64748b; white-space:nowrap;">{{ $br->created_at->format('M d, Y h:i A') }}</td>
            <td style="padding:10px 16px; text-align:center;">
              <span class="badge" style="font-size:.7rem; background:{{ $br->release_status==='Released'?'#dcfce7;color:#166534':($br->release_status==='Partial'?'#dbeafe;color:#1e40af':'#fef3c7;color:#92400e') }};">
                {{ $br->release_status }}
              </span>
            </td>
            <td style="padding:10px 16px;">
              @if ($br->receipt_file)
                <a href="{{ asset('storage/' . $br->receipt_file) }}" target="_blank"
                   style="color:#3b82f6;font-size:.78rem;text-decoration:none;">
                  <i class="bi bi-file-earmark"></i> View
                </a>
              @else
                <span style="color:#cbd5e1;">—</span>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="10" class="text-center text-muted py-4">No disbursements recorded.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
