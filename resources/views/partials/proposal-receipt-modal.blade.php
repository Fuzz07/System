@php
    $receiptUrl = \App\Helpers\SscHelper::getUploadUrl($proposal->completion_proof);
    $modalId = 'proposalReceiptModal' . $proposal->id;
@endphp

@if(!empty($mobile))
<div class="project-receipt-overlay" id="{{ $modalId }}" hidden role="dialog" aria-modal="true" aria-labelledby="{{ $modalId }}Title">
    <div class="project-receipt-dialog">
        <div class="project-receipt-header">
            <div>
                <div class="project-receipt-eyebrow"><i class="bi bi-patch-check-fill"></i> Verified liquidation</div>
                <h2 id="{{ $modalId }}Title">Official Receipt</h2>
            </div>
            <button type="button" class="project-receipt-close" data-receipt-modal-close aria-label="Close receipt preview"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="project-receipt-body">
            <img src="{{ $receiptUrl }}" alt="Official receipt for {{ $proposal->project_title }}" loading="lazy">
            <div class="project-receipt-error" hidden>
                <i class="bi bi-image"></i>
                <p>The receipt preview could not be loaded.</p>
            </div>
        </div>
        <div class="project-receipt-actions">
            <a href="{{ $receiptUrl }}" target="_blank" rel="noopener" class="project-receipt-open"><i class="bi bi-box-arrow-up-right"></i> Open full size</a>
            <button type="button" data-receipt-modal-close>Close</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const modal = document.getElementById(@json($modalId));
    const opener = document.querySelector('[data-receipt-modal-open="' + @json($modalId) + '"]');
    if (!modal || !opener) return;

    const closeButtons = modal.querySelectorAll('[data-receipt-modal-close]');
    const image = modal.querySelector('.project-receipt-body img');
    let previousFocus = null;

    function openReceipt() {
        previousFocus = document.activeElement;
        modal.hidden = false;
        document.body.classList.add('project-receipt-open');
        modal.querySelector('.project-receipt-close').focus();
    }

    function closeReceipt() {
        modal.hidden = true;
        document.body.classList.remove('project-receipt-open');
        if (previousFocus) previousFocus.focus();
    }

    opener.addEventListener('click', openReceipt);
    closeButtons.forEach(function (button) { button.addEventListener('click', closeReceipt); });
    modal.addEventListener('click', function (event) {
        if (event.target === modal) closeReceipt();
    });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !modal.hidden) closeReceipt();
    });
    image.addEventListener('error', function () {
        image.hidden = true;
        modal.querySelector('.project-receipt-error').hidden = false;
    });
})();
</script>
@endpush
@else
<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 overflow-hidden" style="border-radius:var(--radius);">
            <div class="modal-header modal-header-custom">
                <div>
                    <div class="small text-success fw-bold mb-1"><i class="bi bi-patch-check-fill"></i> Verified liquidation</div>
                    <h5 class="modal-title fw-bold" id="{{ $modalId }}Title">Official Receipt</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light text-center">
                <img src="{{ $receiptUrl }}" alt="Official receipt for {{ $proposal->project_title }}" loading="lazy"
                    class="img-fluid rounded-3 border bg-white"
                    style="max-height:68vh;object-fit:contain;"
                    onerror="this.hidden=true;this.nextElementSibling.hidden=false;">
                <div class="py-5 text-muted" hidden>
                    <i class="bi bi-image fs-1 d-block mb-2"></i>
                    The receipt preview could not be loaded. Use the Open full size button below.
                </div>
            </div>
            <div class="modal-footer border-0">
                <a href="{{ $receiptUrl }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm"><i class="bi bi-box-arrow-up-right"></i> Open full size</a>
                <button type="button" class="btn btn-outline-secondary btn-sm px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endif
