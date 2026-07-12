@extends('layouts.app')

@section('sidebar-nav') @include('partials.sidebar-treasurer') @endsection

@section('content')
<style>
  .progress-bar-custom {
    height: 8px;
    border-radius: 99px;
    background: #e2e8f0;
    overflow: hidden;
    margin-top: 6px;
  }

  .progress-fill {
    height: 100%;
    border-radius: 99px;
    background: linear-gradient(90deg, #d97706, #f59e0b);
    transition: width .4s ease;
  }

  .release-badge-pending {
    background: #fffbeb;
    color: #92400e;
    border: 1px solid #fbbf24;
  }

  .release-badge-released {
    background: #f0fdf4;
    color: #166534;
    border: 1px solid #86efac;
  }

  .release-badge-partial {
    background: #eff6ff;
    color: #1e40af;
    border: 1px solid #93c5fd;
  }

  .proposal-card-treasury {
    border: 2px solid transparent;
    border-radius: 16px;
    padding: 18px;
    cursor: pointer;
    transition: all .2s;
    background: #fff;
    box-shadow: 0 1px 4px rgba(0, 0, 0, .06);
    margin-bottom: 12px;
  }

  .proposal-card-treasury:hover {
    border-color: #d97706;
    background: #fffdf5;
  }

  .proposal-card-treasury.selected {
    border-color: #d97706;
    background: #fffbeb;
    box-shadow: 0 0 0 3px rgba(217, 119, 6, .15);
  }

  .tab-btn {
    border: none;
    background: #f1f5f9;
    color: #64748b;
    padding: 8px 20px;
    border-radius: 99px;
    font-weight: 600;
    font-size: .83rem;
    cursor: pointer;
    transition: all .2s;
  }

  .tab-btn.active {
    background: #d97706;
    color: #fff;
  }

  .history-row {
    display: flex;
    gap: 12px;
    align-items: flex-start;
    padding: 12px 0;
    border-bottom: 1px solid #f1f5f9;
  }
</style>

<div class="page-header mb-4">
  <div>
    <h1><i class="bi bi-cash-coin me-2" style="color:#d97706;"></i>Release Budget</h1>
    <p>Disburse approved project budgets to officers</p>
  </div>
</div>

<div class="row g-4">

  <!-- LEFT: Approved Proposals List -->
  <div class="col-lg-5">
    <div class="card">
      <div class="card-header-custom d-flex justify-content-between align-items-center">
        <span class="card-title">Approved Proposals</span>
        <span class="badge bg-warning text-dark">{{ count($proposals) }}</span>
      </div>
      <div style="padding:12px 16px; border-bottom:1px solid #f1f5f9;">
        <form method="GET" action="{{ route('treasurer.release') }}" class="d-flex gap-2">
          <input type="text" name="search" value="{{ $filterSearch }}" placeholder="Search project or officer..."
            class="form-control form-control-sm" style="border-radius:8px; font-size:.82rem;">
          <button type="submit" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">
            <i class="bi bi-search"></i>
          </button>
        </form>
      </div>
      <div style="max-height: calc(100vh - 340px); overflow-y:auto; padding:12px 16px;">
        @forelse ($proposals as $p)
          @php
            $pct = $p->approved_budget > 0 ? min(100, round(($p->total_released / $p->approved_budget) * 100)) : 0;
            $remaining = $p->approved_budget - $p->total_released;
            $isFullyReleased = $remaining <= 0.01;
          @endphp
          <div
            class="proposal-card-treasury {{ (isset($selectedProposal) && $selectedProposal->id == $p->id) ? 'selected' : '' }}"
            onclick="selectProposal({{ $p->id }}, '{{ addslashes($p->project_title) }}', {{ $p->approved_budget }}, {{ $p->total_released }})">
            <div class="d-flex justify-content-between align-items-start">
              <div style="font-size:.85rem; font-weight:700; color:#1e293b; flex:1; padding-right:8px;">
                {{ $p->project_title }}
              </div>
              @if ($isFullyReleased)
                <span
                  style="font-size:.7rem; background:#dcfce7; color:#166534; border:1px solid #86efac; border-radius:99px; padding:2px 8px; white-space:nowrap;">
                  <i class="bi bi-check2-all"></i> Fully Released
                </span>
              @else
                <span
                  style="font-size:.7rem; background:#fef3c7; color:#92400e; border:1px solid #fbbf24; border-radius:99px; padding:2px 8px; white-space:nowrap;">
                  <i class="bi bi-hourglass"></i> Pending
                </span>
              @endif
            </div>
            <div style="font-size:.75rem; color:#64748b; margin-top:4px;">
              {{ $p->officer->fullname ?? '—' }} &bull; Approved:
              <strong>{!! \App\Helpers\SscHelper::formatCurrency($p->approved_budget) !!}</strong>
            </div>
            <div class="progress-bar-custom">
              <div class="progress-fill"
                style="width:{{ $pct }}%;{{ $isFullyReleased ? 'background:linear-gradient(90deg,#059669,#34d399)' : '' }}">
              </div>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:.72rem;color:#94a3b8;margin-top:4px;">
              <span>Released: {!! \App\Helpers\SscHelper::formatCurrency($p->total_released) !!}</span>
              <span>{{ $pct }}% &bull; {{ $p->release_count }} disbursement(s)</span>
            </div>
          </div>
        @empty
          <div class="text-center text-muted py-5">
            <i class="bi bi-inbox" style="font-size:2.5rem;"></i><br>
            No approved proposals found.
          </div>
        @endforelse
      </div>
    </div>
  </div>

  <!-- RIGHT: Release Form + History -->
  <div class="col-lg-7">

    <!-- Tabs -->
    <div class="d-flex gap-2 mb-3">
      <button class="tab-btn active" id="tab-form-btn" onclick="switchTab('form')">
        <i class="bi bi-send me-1"></i> Release Form
      </button>
      <button class="tab-btn" id="tab-history-btn" onclick="switchTab('history')">
        <i class="bi bi-clock-history me-1"></i> Release History
      </button>
    </div>

    <!-- RELEASE FORM -->
    <div id="tab-form" class="card">
      <div class="card-header-custom">
        <span class="card-title"><i class="bi bi-cash-stack me-1" style="color:#d97706;"></i> Disburse Budget</span>
      </div>
      <div style="padding:24px;">

        <!-- Selected Proposal Summary -->
        <div id="proposal-summary"
          style="background:#fffbeb; border:1.5px solid #fcd34d; border-radius:12px; padding:16px; margin-bottom:20px; {{ isset($selectedProposal) ? '' : 'display:none;' }}">
          <div
            style="font-size:.78rem; color:#92400e; font-weight:700; text-transform:uppercase; letter-spacing:.05em; margin-bottom:6px;">
            Selected Project</div>
          <div id="summary-title" style="font-weight:700; color:#1e293b; font-size:.95rem;">
            {{ isset($selectedProposal) ? $selectedProposal->project_title : '' }}
          </div>
          <div class="row g-2 mt-2">
            <div class="col-4">
              <div style="font-size:.72rem; color:#94a3b8;">Approved Budget</div>
              <div id="summary-approved" style="font-weight:700; color:#1e293b; font-size:.88rem;">
                {{ isset($selectedProposal) ? \App\Helpers\SscHelper::formatCurrency($selectedProposal->approved_budget) : '' }}
              </div>
            </div>
            <div class="col-4">
              <div style="font-size:.72rem; color:#94a3b8;">Already Released</div>
              <div id="summary-released" style="font-weight:700; color:#d97706; font-size:.88rem;">
                {{ isset($selectedProposal) ? \App\Helpers\SscHelper::formatCurrency($selectedProposal->total_released) : '' }}
              </div>
            </div>
            <div class="col-4">
              <div style="font-size:.72rem; color:#94a3b8;">Remaining</div>
              <div id="summary-remaining" style="font-weight:700; color:#059669; font-size:.88rem;">
                {{ isset($selectedProposal) ? \App\Helpers\SscHelper::formatCurrency($selectedProposal->approved_budget - $selectedProposal->total_released) : '' }}
              </div>
            </div>
          </div>
        </div>

        <div id="no-selection-hint"
          style="{{ isset($selectedProposal) ? 'display:none;' : '' }}text-align:center; padding:30px; color:#94a3b8;">
          <i class="bi bi-arrow-left-circle" style="font-size:2.5rem; display:block; margin-bottom:8px;"></i>
          Select an approved proposal from the left panel to release its budget.
        </div>

        <form method="POST" action="{{ route('treasurer.release.submit') }}" enctype="multipart/form-data" id="releaseForm"
          style="{{ isset($selectedProposal) ? '' : 'display:none;' }}">
          @csrf
          <input type="hidden" name="proposal_id" id="f-proposal-id"
            value="{{ isset($selectedProposal) ? $selectedProposal->id : '' }}">

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label-custom">Amount to Release (₱) <span style="color:red;">*</span></label>
              <input type="number" name="amount_released" id="f-amount" class="form-control-custom" step="0.01" min="0.01"
                max="{{ isset($selectedProposal) ? ($selectedProposal->approved_budget - $selectedProposal->total_released) : '' }}"
                placeholder="0.00" required>
              <div style="font-size:.72rem; color:#94a3b8; margin-top:4px;">Max releasable: <span
                  id="f-max-label">{{ isset($selectedProposal) ? \App\Helpers\SscHelper::formatCurrency($selectedProposal->approved_budget - $selectedProposal->total_released) : '—' }}</span>
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label-custom">Release Method <span style="color:red;">*</span></label>
              <select name="release_method" id="f-method" class="form-control-custom" required>
                <option value="Cash">💵 Cash</option>
                <option value="Bank Transfer">🏦 Bank Transfer</option>
                <option value="Check">🎫 Check</option>
                <option value="GCash">📱 GCash</option>
                <option value="Maya">📱 Maya</option>
                <option value="Other">Other</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label-custom">Reference / Transaction No.</label>
              <input type="text" name="reference_no" class="form-control-custom"
                placeholder="e.g. GCash ref, check no.">
            </div>

            <div class="col-md-6">
              <label class="form-label-custom">Release Status <span style="color:red;">*</span></label>
              <select name="release_status" class="form-control-custom" required>
                <option value="Released">✅ Released (Full / Final)</option>
                <option value="Partial">🔄 Partial Release</option>
              </select>
            </div>

            <div class="col-12">
              <label class="form-label-custom">Receipt / Proof of Release</label>
              <input type="file" name="receipt" class="form-control" accept=".jpg,.jpeg,.png,.pdf,.mp4"
                style="border-radius:10px; border:1.5px solid #e2e8f0; font-size:.85rem; padding:8px;">
              <div style="font-size:.72rem; color:#94a3b8; margin-top:4px;">Optional. JPG, JPEG, PNG, PDF, or MP4 up to 5MB.</div>
            </div>

            <div class="col-12">
              <label class="form-label-custom">Treasurer Notes</label>
              <textarea name="notes" class="form-control-custom" rows="3"
                placeholder="Enter disbursement notes, conditions, or acknowledgment details..."></textarea>
            </div>

            <div class="col-12 d-flex gap-2 justify-content-end pt-2">
              <button type="button" onclick="clearSelection()" class="btn btn-outline-secondary"
                style="border-radius:10px;">
                <i class="bi bi-x-circle"></i> Clear
              </button>
              <button type="button" class="btn-primary-custom"
                style="background:linear-gradient(135deg,#d97706,#f59e0b);border:none;border-radius:10px;padding:10px 28px;"
                onclick="openReleaseConfirmation()">
                <i class="bi bi-cash-coin"></i> Confirm & Release Budget
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- HISTORY TAB -->
    <div id="tab-history" class="card" style="display:none;">
      <div class="card-header-custom d-flex justify-content-between align-items-center">
        <span class="card-title"><i class="bi bi-clock-history me-1"></i> Disbursement History</span>
        <span class="badge bg-secondary">{{ count($allReleases) }}</span>
      </div>
      <div style="max-height: calc(100vh - 340px); overflow-y:auto; padding: 8px 20px;">
        @forelse ($allReleases as $br)
          <div class="history-row">
            <div
              style="width:38px;height:38px;background:#fef3c7;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <i class="bi bi-cash" style="color:#d97706;"></i>
            </div>
            <div style="flex:1;">
              <div style="display:flex;justify-content:space-between;align-items:baseline;">
                <div style="font-size:.85rem;font-weight:700;color:#1e293b;">{{ $br->proposal->project_title ?? 'Unknown' }}</div>
                <div style="font-size:.82rem;font-weight:800;color:#059669;">{!! \App\Helpers\SscHelper::formatCurrency($br->amount_released) !!}
                </div>
              </div>
              <div style="font-size:.75rem;color:#64748b;margin-top:2px;">
                <i class="bi bi-person"></i> {{ $br->proposal->officer->fullname ?? '—' }}
                &bull; <i class="bi bi-credit-card"></i> {{ $br->release_method }}
                @if ($br->reference_no)
                  &bull; Ref: {{ $br->reference_no }}
                @endif
                &bull; {!! \App\Helpers\SscHelper::timeAgo($br->created_at) !!}
              </div>
              @if ($br->notes)
                <div style="font-size:.72rem;color:#94a3b8;margin-top:2px;font-style:italic;">{{ $br->notes }}</div>
              @endif
              @if ($br->receipt_file)
                <div style="margin-top:4px;">
                  <a href="{{ asset('storage/' . $br->receipt_file) }}" target="_blank"
                    style="font-size:.72rem;color:#3b82f6;text-decoration:none;">
                    <i class="bi bi-file-earmark-pdf"></i> View Receipt
                  </a>
                </div>
              @endif
            </div>
            <div>
              <span class="badge"
                style="font-size:.7rem; background:{{ $br->release_status === 'Released' ? '#dcfce7;color:#166534' : ($br->release_status === 'Partial' ? '#dbeafe;color:#1e40af' : '#fef3c7;color:#92400e') }}px;">
                {{ $br->release_status }}
              </span>
            </div>
          </div>
        @empty
          <div class="text-center text-muted py-5">No disbursements recorded yet.</div>
        @endforelse
      </div>
    </div>

  </div>
</div>

<!-- Release Confirmation Modal -->
<div class="modal fade" id="releaseConfirmModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:20px; border:none; overflow:hidden;">
      <div class="modal-header modal-header-custom" style="background: linear-gradient(135deg, #0d2b5c 0%, #1e293b 100%); color:#fff;">
        <h5 class="modal-title"><i class="bi bi-shield-check me-2"></i> Confirm Budget Release</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <p class="text-muted" style="font-size:.95rem;">Please review the release details before submitting. This action will be recorded in the budget release ledger.</p>
        <ul class="list-unstyled" style="font-size:.94rem; line-height:1.8;">
          <li><strong>Project:</strong> <span id="conf-project-title"></span></li>
          <li><strong>Release Amount:</strong> <span id="conf-amount"></span></li>
          <li><strong>Method:</strong> <span id="conf-method"></span></li>
          <li><strong>Status:</strong> <span id="conf-status"></span></li>
          <li><strong>Reference:</strong> <span id="conf-reference"></span></li>
          <li><strong>Notes:</strong> <span id="conf-notes"></span></li>
        </ul>
        <div class="alert alert-warning" role="alert" style="border-radius:14px; font-size: 0.85rem;">
          <i class="bi bi-exclamation-triangle-fill"></i> This release will be logged and cannot be undone from the form.
        </div>
      </div>
      <div class="modal-footer border-0 p-4 pt-0">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn-primary-custom" style="border-radius:12px; padding:10px 24px; background: linear-gradient(135deg, #d97706, #f59e0b); border: none;" onclick="submitReleaseForm()">
          <i class="bi bi-check-circle"></i> Confirm & Release
        </button>
      </div>
    </div>
  </div>
</div>

<script>
  // Tab switching
  function switchTab(t) {
    document.getElementById('tab-form').style.display = t === 'form' ? '' : 'none';
    document.getElementById('tab-history').style.display = t === 'history' ? '' : 'none';
    document.getElementById('tab-form-btn').classList.toggle('active', t === 'form');
    document.getElementById('tab-history-btn').classList.toggle('active', t === 'history');
  }

  // Select a proposal from the left list
  function selectProposal(id, title, approved, released) {
    var remaining = approved - released;
    document.getElementById('f-proposal-id').value = id;
    document.getElementById('f-amount').value = '';
    document.getElementById('f-amount').max = remaining;
    document.getElementById('summary-title').textContent = title;
    document.getElementById('summary-approved').textContent = '₱' + Number(approved).toLocaleString('en-PH', { minimumFractionDigits: 2 });
    document.getElementById('summary-released').textContent = '₱' + Number(released).toLocaleString('en-PH', { minimumFractionDigits: 2 });
    document.getElementById('summary-remaining').textContent = '₱' + Number(remaining).toLocaleString('en-PH', { minimumFractionDigits: 2 });
    document.getElementById('f-max-label').textContent = '₱' + Number(remaining).toLocaleString('en-PH', { minimumFractionDigits: 2 });
    document.getElementById('proposal-summary').style.display = '';
    document.getElementById('no-selection-hint').style.display = 'none';
    document.getElementById('releaseForm').style.display = '';

    // Highlight selected card
    document.querySelectorAll('.proposal-card-treasury').forEach(el => el.classList.remove('selected'));
    event.currentTarget.classList.add('selected');

    // Scroll to form
    document.getElementById('releaseForm').scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  function clearSelection() {
    document.getElementById('f-proposal-id').value = '';
    document.getElementById('proposal-summary').style.display = 'none';
    document.getElementById('no-selection-hint').style.display = '';
    document.getElementById('releaseForm').style.display = 'none';
    document.querySelectorAll('.proposal-card-treasury').forEach(el => el.classList.remove('selected'));
  }

  function openReleaseConfirmation() {
    var amt = parseFloat(document.getElementById('f-amount').value) || 0;
    var title = document.getElementById('summary-title').textContent.trim();
    var method = document.getElementById('f-method').value;
    var status = document.querySelector('select[name="release_status"]').value;
    var reference = document.querySelector('input[name="reference_no"]').value.trim();
    var notes = document.querySelector('textarea[name="notes"]').value.trim();

    if (!title) {
      alert('Select a proposal before releasing budget.');
      return;
    }
    if (amt <= 0) {
      alert('Please enter a valid amount to release.');
      return;
    }

    document.getElementById('conf-project-title').textContent = title;
    document.getElementById('conf-amount').textContent = '₱' + amt.toLocaleString('en-PH', { minimumFractionDigits: 2 });
    document.getElementById('conf-method').textContent = method;
    document.getElementById('conf-status').textContent = status === 'Released' ? 'Released (Full / Final)' : 'Partial Release';
    document.getElementById('conf-reference').textContent = reference || 'Not provided';
    document.getElementById('conf-notes').textContent = notes || 'No notes provided';

    var confirmModal = new bootstrap.Modal(document.getElementById('releaseConfirmModal'));
    confirmModal.show();
  }

  function submitReleaseForm() {
    document.getElementById('releaseForm').submit();
  }
</script>
@endsection
