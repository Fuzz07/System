{{--
    Pop-up showing an expense's receipt beside the expense it backs, so it can
    be read without leaving the page. Open it with a button whose
    data-bs-target is "#receiptModal{expense id}". Pass $ex, an expense with a
    receipt and its budget loaded.
    Pass $showPeople = true to add who filed it and who reviewed it.
    Pass $reviewModal (an element id) to add a Review button that opens it.
--}}
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
                            @if(!empty($showPeople))
                            <dt class="text-muted small text-uppercase" style="letter-spacing:.04em;">Filed By</dt>
                            <dd>{{ $ex->officer->fullname ?? 'N/A' }}</dd>
                            @if($ex->approver)
                            <dt class="text-muted small text-uppercase" style="letter-spacing:.04em;">Reviewed By</dt>
                            <dd>{{ $ex->approver->fullname }}</dd>
                            @endif
                            @endif
                            <dt class="text-muted small text-uppercase" style="letter-spacing:.04em;">Filed</dt>
                            <dd>{{ $ex->created_at?->format('M d, Y h:i A') ?? '—' }}</dd>
                            @if($ex->description)
                            <dt class="text-muted small text-uppercase" style="letter-spacing:.04em;">Description</dt>
                            <dd style="white-space:pre-line;">{{ $ex->description }}</dd>
                            @endif
                            @if($ex->admin_notes)
                            <dt class="text-muted small text-uppercase" style="letter-spacing:.04em;">Review Notes</dt>
                            <dd class="mb-0" style="white-space:pre-line;">{{ $ex->admin_notes }}</dd>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <a href="{{ $receiptUrl }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm"><i class="bi bi-box-arrow-up-right"></i> Open full size</a>
                <button type="button" class="btn btn-outline-secondary btn-sm px-4" data-bs-dismiss="modal">Close</button>
                @if(!empty($reviewModal))
                {{-- Bootstrap closes this pop-up before opening the review form. --}}
                <button type="button" class="btn-primary-custom btn-sm" data-bs-toggle="modal" data-bs-target="#{{ $reviewModal }}"><i class="bi bi-pencil-square"></i> Review</button>
                @endif
            </div>
        </div>
    </div>
</div>
