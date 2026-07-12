@extends('layouts.app')

@section('sidebar-nav') @include('partials.sidebar-treasurer') @endsection

@section('content')
<style>
  body { overflow: hidden; }
  .page-content {
    height: calc(100vh - 70px);
    display: flex; flex-direction: column;
    padding: 12px 24px !important; overflow: hidden;
  }
  .main-grid-container { flex: 1; overflow: hidden; margin-top: 0; min-height: 0; }
  .recent-list { max-height: calc(100vh - 420px); overflow-y: auto; scrollbar-width: thin; }
  .recent-list::-webkit-scrollbar { width: 5px; }
  .recent-list::-webkit-scrollbar-thumb { background: var(--slate-200); border-radius: 10px; }
  .dashboard-banner { margin-top: 0; margin-bottom: 1rem !important; padding: 1.25rem !important; }

  .release-badge-pending  { background: #fffbeb; color: #92400e; border: 1px solid #fbbf24; border-radius: 20px; padding: 2px 10px; font-size: .72rem; font-weight: 700; }
  .release-badge-released { background: #f0fdf4; color: #166534; border: 1px solid #86efac; border-radius: 20px; padding: 2px 10px; font-size: .72rem; font-weight: 700; }
  .release-badge-partial  { background: #eff6ff; color: #1e40af; border: 1px solid #93c5fd; border-radius: 20px; padding: 2px 10px; font-size: .72rem; font-weight: 700; }

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
  .card-header-custom {
    padding: 10px 16px !important;
  }
  .card-title {
    font-size: 0.9rem !important;
  }
  .row.g-3 {
    margin-bottom: 12px !important;
    --bs-gutter-y: 12px;
  }
</style>

<!-- Banner -->
<div class="dashboard-banner d-flex align-items-center justify-content-between rounded-3"
  style="background: linear-gradient(135deg, #fef3c7 0%, #fff7ed 100%); color: #0f172a; border: 1px solid #fed7aa; position: relative;">
  <div style="position: relative; z-index: 1;">
    <h1 style="font-size: 1.25rem; font-weight: 800; margin: 0; color: #78350f;">Treasurer Control Panel</h1>
    <p style="margin: 4px 0 0; opacity: .7; font-size: .8rem;">School Year {{ $sy }} — Budget Release Management</p>
  </div>
  <div style="font-size: 4rem; font-weight: 900; opacity: .06; position: absolute; right: 20px; top: -10px; font-family: monospace; user-select: none;">₱</div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-3">
  <div class="col-sm-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon" style="background: rgba(13, 43, 92, 0.1); color: #0d2b5c;"><i class="bi bi-wallet2"></i></div>
      <div class="stat-info">
        <div class="label">Total Allocated Budget</div>
        <div class="value" data-count="{{ $totalBudget }}" data-currency="1">₱0.00</div>
        <div class="sub" style="font-size: 0.65rem; color: #64748b;">School Year {{ $sy }}</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon" style="background: rgba(217, 119, 6, 0.1); color: #d97706;"><i class="bi bi-cash-coin"></i></div>
      <div class="stat-info">
        <div class="label">Total Released</div>
        <div class="value" data-count="{{ $totalReleased }}" data-currency="1">₱0.00</div>
        <div class="sub" style="font-size: 0.65rem; color: #64748b;">Disbursed to officers</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon" style="background: rgba(0, 168, 120, 0.1); color: #00a878;"><i class="bi bi-hourglass-split"></i></div>
      <div class="stat-info">
        <div class="label">Pending Release</div>
        <div class="value" data-count="{{ $pendingRelease }}">0</div>
        <div class="sub" style="font-size: 0.65rem; color: #64748b;">Approved proposals</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon" style="background: rgba(5, 150, 105, 0.1); color: #059669;"><i class="bi bi-check2-all"></i></div>
      <div class="stat-info">
        <div class="label">Fully Released</div>
        <div class="value" data-count="{{ $releasedCount }}">0</div>
        <div class="sub" style="font-size: 0.65rem; color: #64748b;">Completed disbursements</div>
      </div>
    </div>
  </div>
</div>

<!-- Main Grid -->
<div class="main-grid-container row g-3">
  <!-- Left: Awaiting Release -->
  <div class="col-xl-7 col-lg-6 d-flex flex-column">
    <div class="card h-100 d-flex flex-column">
      <div class="card-header-custom d-flex justify-content-between align-items-center">
        <span class="card-title mb-0"><i class="bi bi-hourglass-split me-1"></i> Awaiting Budget Release</span>
        <a href="{{ route('treasurer.release') }}" class="btn btn-sm btn-outline-warning" style="border-radius: 8px; font-size: 0.75rem; font-weight: 600; padding: 4px 12px;">Release Budget</a>
      </div>
      <div class="recent-list flex-grow-1" style="padding: 0 0 8px;">
        @forelse ($awaitingRelease as $p)
          @php $remaining = $p->approved_budget - $p->total_released; @endphp
          <div class="px-4 py-3 border-bottom d-flex gap-3 align-items-start">
            <div style="width: 36px; height: 36px; background: #fffbeb; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i class="bi bi-file-earmark-check" style="color: #d97706; font-size: .9rem;"></i>
            </div>
            <div style="flex: 1;">
              <div style="display: flex; justify-content: space-between; align-items: baseline;">
                <div style="font-size: .83rem; font-weight: 700; color: #2d3748;">{{ $p->project_title }}</div>
                <div style="font-size: .78rem; font-weight: 700; color: #d97706;">{!! \App\Helpers\SscHelper::formatCurrency($remaining) !!} remaining</div>
              </div>
              <div style="font-size: .75rem; color: #718096; margin-top: 2px;">
                Officer: {{ $p->officer->fullname ?? '—' }} &bull; Approved: {!! \App\Helpers\SscHelper::formatCurrency($p->approved_budget) !!}
                @if ($p->total_released > 0)
                  &bull; Released: {!! \App\Helpers\SscHelper::formatCurrency($p->total_released) !!}
                @endif
              </div>
            </div>
            <a href="{{ route('treasurer.release', ['proposal_id' => $p->id]) }}" class="btn btn-sm" 
               style="background: #d97706; color: #fff; border: none; border-radius: 8px; font-size: .78rem; padding: 4px 14px; white-space: nowrap;">
              <i class="bi bi-send"></i> Release
            </a>
          </div>
        @empty
          <div class="text-center text-muted py-5">
            <i class="bi bi-check2-circle" style="font-size: 2.5rem; color: #a0aec0;"></i>
            <div class="mt-2" style="font-size: 0.85rem;">All approved budgets have been released!</div>
          </div>
        @endforelse
      </div>
    </div>
  </div>

  <!-- Right: Recent Releases -->
  <div class="col-xl-5 col-lg-6 d-flex flex-column">
    <div class="card h-100 d-flex flex-column">
      <div class="card-header-custom d-flex justify-content-between align-items-center">
        <span class="card-title mb-0">Recent Disbursements</span>
        <a href="{{ route('treasurer.reports') }}" style="font-size: 0.75rem; text-decoration: none;">View All <i class="bi bi-arrow-right"></i></a>
      </div>
      <div class="recent-list flex-grow-1" style="padding: 0 0 8px;">
        @forelse ($recentReleases as $br)
          <div class="px-4 py-2 border-bottom" style="display: flex; gap: 10px; align-items: flex-start;">
            <div style="width: 28px; height: 28px; background: #f0fdf4; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: .7rem;">
              <i class="bi bi-cash" style="color: #059669;"></i>
            </div>
            <div style="flex: 1;">
              <div style="display: flex; justify-content: space-between; align-items: baseline;">
                <div style="font-size: .8rem; font-weight: 600; color: #2d3748; max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $br->proposal->project_title ?? 'Unknown' }}</div>
                <div style="font-size: .75rem; font-weight: 700; color: #059669;">{!! \App\Helpers\SscHelper::formatCurrency($br->amount_released) !!}</div>
              </div>
              <div style="font-size: .72rem; color: #718096; display: flex; justify-content: space-between; margin-top: 2px;">
                <span>{!! \App\Helpers\SscHelper::timeAgo($br->created_at) !!}</span>
                <span class="release-badge-{{ strtolower($br->release_status) }}">{{ $br->release_status }}</span>
              </div>
            </div>
          </div>
        @empty
          <div class="text-center text-muted py-5">
            <i class="bi bi-inbox" style="font-size: 2rem; color: #cbd5e1;"></i>
            <div class="mt-2" style="font-size: 0.85rem;">No disbursements yet.</div>
          </div>
        @endforelse
      </div>
    </div>
  </div>
</div>
@endsection
