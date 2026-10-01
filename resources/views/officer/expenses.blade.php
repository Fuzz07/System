@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-officer') @endsection

@section('content')
<div class="page-header"><div><h1>My Expenses</h1><p>Submit and track expense reports with receipts</p></div>
    <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#expenseModal"><i class="bi bi-plus-circle"></i> File Expense</button>
</div>

<div class="card"><div class="table-responsive-custom"><table class="table-custom">
    <thead><tr><th>#</th><th>Expense Title</th><th>Budget Fund</th><th>Amount</th><th>Receipt</th><th>Status</th><th>Notes</th><th>Date</th><th style="text-align:center;">Actions</th></tr></thead>
    <tbody>
    @forelse($expenses as $i => $ex)
    <tr>
        <td style="color:#a0aec0;font-size:.8rem;">{{ $expenses->firstItem() + $i }}</td>
        <td><div style="font-weight:700;">{{ $ex->expense_title }}</div><div style="font-size:.75rem;color:#718096;">{{ Str::limit($ex->description, 60) }}</div></td>
        <td><span class="badge bg-primary" style="font-size:.7rem;">{{ $ex->budget->title ?? 'N/A' }}</span></td>
        <td style="font-weight:700;color:var(--danger);">{!! \App\Helpers\SscHelper::formatCurrency($ex->amount) !!}</td>
        <td>@if($ex->receipt)<button type="button" class="btn btn-outline-primary btn-sm" style="font-size:.72rem;" data-bs-toggle="modal" data-bs-target="#receiptModal{{ $ex->id }}"><i class="bi bi-file-earmark"></i> View</button>@else —@endif</td>
        <td>{!! \App\Helpers\SscHelper::statusBadge($ex->status) !!}</td>
        <td style="font-size:.78rem;color:#718096;max-width:140px;">{{ $ex->admin_notes ?? '—' }}</td>
        <td style="font-size:.78rem;white-space:nowrap;">{{ $ex->created_at?->format('M d, Y') }}</td>
        <td style="text-align:center;">
            @if($ex->status === 'Pending')
            <form method="POST" action="{{ route('officer.expenses.destroy', $ex) }}" data-confirm="Are you sure you want to cancel and delete this pending expense?" style="display:inline-block;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm" style="font-size:.72rem; padding: 2px 8px;"><i class="bi bi-trash"></i> Cancel</button>
            </form>
            @else
            <span class="text-muted small" style="font-size:.72rem;"><i class="bi bi-lock-fill"></i> Locked</span>
            @endif
        </td>
    </tr>
    @empty
    <tr><td colspan="9" class="text-center py-5 text-muted">No expenses filed yet.</td></tr>
    @endforelse
    </tbody>
</table></div></div>
{{ $expenses->withQueryString()->links('partials.pagination') }}

{{-- Receipt Modals: the receipt and the expense it backs, without leaving the page. --}}
@foreach($expenses->getCollection()->filter->receipt as $ex)
@php
    $receiptUrl = \App\Helpers\SscHelper::getUploadUrl($ex->receipt);
    $isImage = (bool) preg_match('/\.(png|jpe?g|gif|webp)(\?|$)/i', $receiptUrl);
@endphp
<div class="modal fade" id="receiptModal{{ $ex->id }}" tabindex="-1" aria-labelledby="receiptModalTitle{{ $ex->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:var(--radius);border:none;overflow:hidden;">
            <div class="modal-header modal-header-custom">
                <h5 class="modal-title" id="receiptModalTitle{{ $ex->id }}" style="font-weight:700;"><i class="bi bi-receipt"></i> {{ $ex->expense_title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <div class="col-md-7">
                        @if($isImage)
                            <a href="{{ $receiptUrl }}" target="_blank" rel="noopener" title="Open full size"
                                class="d-block rounded-4 overflow-hidden border bg-light text-center">
                                <img src="{{ $receiptUrl }}" alt="Receipt for {{ $ex->expense_title }}" loading="lazy"
                                    style="max-width:100%;max-height:60vh;object-fit:contain;display:block;margin:0 auto;"
                                    onerror="this.replaceWith(Object.assign(document.createElement('div'), { className: 'py-5 text-muted small', textContent: 'The receipt image could not be loaded.' }))">
                            </a>
                        @else
                            <div class="rounded-4 border bg-light d-flex flex-column align-items-center justify-content-center text-center p-5 h-100">
                                <i class="bi bi-file-earmark-text text-primary" style="font-size:2.5rem;"></i>
                                <div class="small text-muted mt-2 mb-3">This receipt can't be previewed here.</div>
                                <a href="{{ $receiptUrl }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm"><i class="bi bi-box-arrow-up-right"></i> Open receipt</a>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-5">
                        <dl class="mb-0" style="font-size:.85rem;">
                            <dt class="text-muted small text-uppercase" style="letter-spacing:.04em;">Amount</dt>
                            <dd class="fw-bold fs-5" style="color:var(--danger);">{!! \App\Helpers\SscHelper::formatCurrency($ex->amount) !!}</dd>
                            <dt class="text-muted small text-uppercase" style="letter-spacing:.04em;">Budget Fund</dt>
                            <dd>{{ $ex->budget->title ?? 'N/A' }}</dd>
                            <dt class="text-muted small text-uppercase" style="letter-spacing:.04em;">Status</dt>
                            <dd>{!! \App\Helpers\SscHelper::statusBadge($ex->status) !!}</dd>
                            <dt class="text-muted small text-uppercase" style="letter-spacing:.04em;">Filed</dt>
                            <dd>{{ $ex->created_at?->format('M d, Y h:i A') ?? '—' }}</dd>
                            @if($ex->description)
                            <dt class="text-muted small text-uppercase" style="letter-spacing:.04em;">Description</dt>
                            <dd style="white-space:pre-line;">{{ $ex->description }}</dd>
                            @endif
                            @if($ex->admin_notes)
                            <dt class="text-muted small text-uppercase" style="letter-spacing:.04em;">Admin Notes</dt>
                            <dd class="mb-0" style="white-space:pre-line;">{{ $ex->admin_notes }}</dd>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <a href="{{ $receiptUrl }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm"><i class="bi bi-box-arrow-up-right"></i> Open full size</a>
                <button type="button" class="btn btn-outline-secondary btn-sm px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endforeach

<div class="modal fade" id="expenseModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content" style="border-radius:var(--radius);border:none;">
    <div class="modal-header modal-header-custom"><h5 class="modal-title" style="font-weight:700;"><i class="bi bi-receipt"></i> File New Expense</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST" action="{{ route('officer.expenses.store') }}" enctype="multipart/form-data">@csrf
        <div class="modal-body p-4"><div class="row g-3">
            <div class="col-md-8"><label class="form-label-custom">Expense Title <span class="text-danger">*</span></label><input type="text" name="expense_title" class="form-control-custom" placeholder="e.g. Sound System Rental" required></div>
            <div class="col-md-4"><label class="form-label-custom">Amount (₱) <span class="text-danger">*</span></label><input type="number" name="amount" class="form-control-custom" placeholder="0.00" min="1" max="100000" step="0.01" required></div>
            <div class="col-12"><label class="form-label-custom">Budget Fund <span class="text-danger">*</span></label><select name="budget_id" class="form-select-custom" required><option value="">Select approved budget fund...</option>@foreach($budgets as $b)<option value="{{ $b->id }}">{{ $b->title }} — Remaining: {!! \App\Helpers\SscHelper::formatCurrency($b->remaining_balance) !!}</option>@endforeach</select></div>
            <div class="col-12"><label class="form-label-custom">Description</label><textarea name="description" class="form-control-custom" rows="3" style="resize:vertical;"></textarea></div>
            <div class="col-12"><label class="form-label-custom">Receipt</label><input type="file" name="receipt" class="form-control-custom" accept="{{ \App\Support\UploadValidation::ACCEPT }}" style="padding:8px 14px;"><div style="font-size:.72rem;color:#a0aec0;margin-top:4px;">PNG, JPG or JPEG only — Max 5MB</div></div>
        </div></div>
        <div class="modal-footer border-0 pt-0"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn-primary-custom"><i class="bi bi-send"></i> Submit Expense</button></div>
    </form>
</div></div></div>
@endsection
