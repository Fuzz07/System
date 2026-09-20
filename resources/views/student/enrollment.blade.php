@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-student') @endsection

@section('content')
@php
    $methodLabels = [
        'gcash' => 'Manual GCash',
        'instapay' => 'InstaPay transfer',
        'paymongo' => 'PayMongo' . ($payment?->paymongo_payment_method ? ' ? ' . strtoupper(str_replace('_', ' ', $payment->paymongo_payment_method)) : ''),
        'walk_in' => 'Walk-in payment',
    ];
    $paymentMethodLabel = $payment ? ($methodLabels[$payment->method] ?? ucfirst($payment->method)) : null;
    $manualMethod = old('payment_method', in_array($payment?->method, ['gcash', 'instapay'], true) ? $payment->method : 'gcash');
@endphp

<style>
    .payment-shell { max-width: 980px; margin: 0 auto; }
    .payment-hero { background: linear-gradient(135deg, #172554 0%, #1d4ed8 60%, #2563eb 100%); border-radius: 24px; color: #fff; padding: 28px; position: relative; overflow: hidden; box-shadow: 0 18px 45px rgba(30, 64, 175, .18); }
    .payment-hero::after { content: ''; position: absolute; width: 220px; height: 220px; border-radius: 50%; right: -70px; top: -105px; background: rgba(255,255,255,.09); }
    .fee-amount { font-size: clamp(2rem, 5vw, 3.25rem); line-height: 1; font-weight: 800; letter-spacing: -.04em; }
    .term-chip { display: inline-flex; align-items: center; gap: 7px; padding: 7px 12px; border-radius: 999px; background: rgba(255,255,255,.13); border: 1px solid rgba(255,255,255,.2); font-size: .82rem; font-weight: 700; }
    .payment-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 20px; padding: 24px; box-shadow: 0 8px 28px rgba(15, 23, 42, .05); }
    .option-heading { font-size: 1.05rem; font-weight: 800; color: #0f172a; }
    .option-copy { color: #64748b; font-size: .9rem; line-height: 1.55; }
    .secure-mark { width: 46px; height: 46px; border-radius: 14px; display: grid; place-items: center; color: #fff; font-size: 1.25rem; background: linear-gradient(135deg, #2563eb, #4338ca); box-shadow: 0 8px 18px rgba(37, 99, 235, .22); }
    .method-pills { display: flex; flex-wrap: wrap; gap: 8px; }
    .method-pill { border: 1px solid #dbeafe; background: #eff6ff; color: #1e40af; border-radius: 999px; padding: 5px 10px; font-size: .75rem; font-weight: 700; }
    .paymongo-button { border: 0; border-radius: 13px; min-height: 50px; padding: 12px 22px; color: #fff; background: linear-gradient(135deg, #2563eb, #4f46e5); font-weight: 800; box-shadow: 0 9px 20px rgba(37, 99, 235, .22); transition: transform .18s ease, box-shadow .18s ease; }
    .paymongo-button:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 12px 24px rgba(37, 99, 235, .28); }
    .paymongo-button:disabled { opacity: .55; cursor: not-allowed; }
    .checkout-note { display: flex; align-items: center; gap: 7px; color: #64748b; font-size: .78rem; }
    .payment-divider { display: flex; align-items: center; gap: 14px; color: #94a3b8; font-size: .76rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
    .payment-divider::before, .payment-divider::after { content: ''; flex: 1; height: 1px; background: #e2e8f0; }
    .manual-choice { position: relative; flex: 1 1 210px; }
    .manual-choice input { position: absolute; opacity: 0; pointer-events: none; }
    .manual-choice label { display: flex; align-items: center; gap: 12px; border: 1.5px solid #e2e8f0; border-radius: 14px; padding: 14px 16px; cursor: pointer; transition: .18s ease; background: #fff; }
    .manual-choice input:checked + label { border-color: #2563eb; background: #eff6ff; box-shadow: 0 0 0 3px rgba(37, 99, 235, .08); }
    .instruction-panel { border: 1px solid #e2e8f0; border-radius: 16px; padding: 18px; background: #f8fafc; color: #475569; font-size: .9rem; line-height: 1.65; }
    .instruction-panel ol { margin: 0; padding-left: 1.2rem; }
    .reference-code { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; background: #fef3c7; color: #92400e; border-radius: 6px; padding: 2px 7px; font-weight: 800; }
    .status-panel { border-radius: 16px; padding: 16px 18px; display: flex; align-items: flex-start; gap: 12px; }
    .status-paid { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; }
    .status-pending { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }
    @media (max-width: 767px) { .payment-hero, .payment-card { padding: 20px; border-radius: 18px; } .paymongo-button { width: 100%; } }
</style>

<div class="container-fluid py-2">
    <div class="payment-shell">
        <section class="payment-hero mb-4" aria-labelledby="enrollment-fee-title">
            <div class="position-relative" style="z-index:1;">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-4">
                    <div>
                        <span class="term-chip mb-3"><i class="bi bi-calendar3"></i> {{ $currentSy }}</span>
                        <h1 id="enrollment-fee-title" class="h4 fw-bold mb-2">Semester Enrollment Fee</h1>
                        <p class="mb-0 opacity-75">Complete your payment securely or submit a manual transfer receipt.</p>
                    </div>
                    <div class="text-md-end">
                        <div class="small text-uppercase fw-bold opacity-75 mb-2" style="letter-spacing:.1em;">Amount due</div>
                        <div class="fee-amount">{{ \App\Helpers\SscHelper::formatCurrency($amount) }}</div>
                    </div>
                </div>
            </div>
        </section>

        @foreach (['success' => 'success', 'info' => 'info', 'error' => 'danger', 'danger' => 'danger'] as $key => $type)
            @if(session($key))
                <div class="alert alert-{{ $type }} d-flex align-items-center gap-2 border-0 shadow-sm" role="alert">
                    <i class="bi {{ $type === 'success' ? 'bi-check-circle-fill' : ($type === 'danger' ? 'bi-exclamation-triangle-fill' : 'bi-info-circle-fill') }}"></i>
                    <span>{{ session($key) }}</span>
                </div>
            @endif
        @endforeach

        @if(request('paymongo') === 'cancelled')
            <div class="alert alert-secondary border-0 shadow-sm"><i class="bi bi-arrow-left-circle me-2"></i>Checkout was closed. You were not charged, and you may continue whenever you are ready.</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger border-0 shadow-sm">
                <div class="fw-bold mb-1">Please check your submission:</div>
                <ul class="mb-0 ps-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        @if($payment && $payment->status === 'paid')
            <div class="payment-card">
                <div class="status-panel status-paid">
                    <i class="bi bi-check-circle-fill fs-4"></i>
                    <div class="flex-grow-1">
                        <div class="fw-bold fs-5">Payment complete</div>
                        <div class="mt-1">Your enrollment fee for {{ $currentSy }} has been confirmed.</div>
                        <div class="small mt-3 d-flex flex-wrap gap-3">
                            <span><strong>Method:</strong> {{ $paymentMethodLabel }}</span>
                            <span><strong>Reference:</strong> {{ $payment->reference }}</span>
                            @if($payment->paid_at)<span><strong>Paid:</strong> {{ $payment->paid_at->format('M d, Y ? h:i A') }}</span>@endif
                        </div>
                    </div>
                </div>
            </div>
        @else
            @if($payment)
                <div class="status-panel status-pending mb-3">
                    <i class="bi bi-clock-history fs-5"></i>
                    <div><strong>Payment pending</strong><div class="small mt-1">{{ $paymentMethodLabel }} ? Reference {{ $payment->reference }}</div></div>
                </div>
            @endif

            <div class="payment-card mb-4">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center gap-4">
                    <div class="secure-mark flex-shrink-0"><i class="bi bi-shield-lock-fill"></i></div>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                            <div class="option-heading">Pay online with PayMongo</div>
                            <span class="badge text-bg-primary rounded-pill">Recommended</span>
                        </div>
                        <div class="option-copy mb-3">Use PayMongo's secure hosted checkout. Confirmation is automatic, so no receipt upload is required.</div>
                        <div class="method-pills">
                            <span class="method-pill">GCash</span><span class="method-pill">QR Ph</span><span class="method-pill">Visa / Mastercard</span>
                        </div>
                    </div>
                    <div class="text-lg-end flex-shrink-0">
                        <form method="POST" action="{{ route('student.enrollment.paymongo.checkout') }}" class="paymongo-form">
                            @csrf
                            <button type="submit" class="paymongo-button" {{ $paymongoEnabled ? '' : 'disabled' }}>
                                <i class="bi bi-lock-fill me-2"></i>{{ $payment?->method === 'paymongo' ? 'Continue checkout' : 'Pay securely' }}
                            </button>
                        </form>
                        <div class="checkout-note mt-2 justify-content-lg-end"><i class="bi bi-box-arrow-up-right"></i> Opens secure PayMongo checkout</div>
                        @unless($paymongoEnabled)<div class="small text-danger mt-2">Online checkout is not configured yet.</div>@endunless
                    </div>
                </div>
            </div>

            <div class="payment-divider my-4"><span>or pay by manual transfer</span></div>

            <div class="payment-card">
                <div class="option-heading mb-1">Submit a transfer receipt</div>
                <div class="option-copy mb-4">Manual payments are reviewed by the SSC office before your status is updated.</div>

                <form method="POST" action="{{ route('student.enrollment.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="d-flex flex-wrap gap-3 mb-4">
                        <div class="manual-choice">
                            <input type="radio" name="payment_method" id="method_gcash" value="gcash" {{ $manualMethod === 'gcash' ? 'checked' : '' }}>
                            <label for="method_gcash"><i class="bi bi-phone fs-4 text-primary"></i><span><strong class="d-block">GCash</strong><small class="text-muted">Direct wallet transfer</small></span></label>
                        </div>
                        <div class="manual-choice">
                            <input type="radio" name="payment_method" id="method_instapay" value="instapay" {{ $manualMethod === 'instapay' ? 'checked' : '' }}>
                            <label for="method_instapay"><i class="bi bi-bank fs-4 text-success"></i><span><strong class="d-block">InstaPay</strong><small class="text-muted">Bank or e-wallet transfer</small></span></label>
                        </div>
                    </div>

                    <div id="instructions_gcash" class="instruction-panel mb-4">
                        <div class="fw-bold text-dark mb-2"><i class="bi bi-wallet2 text-primary me-2"></i>GCash transfer details</div>
                        <ol>
                            <li>Send exactly <strong>{{ \App\Helpers\SscHelper::formatCurrency($amount) }}</strong> to <strong>{{ config('ssc.gcash_number') ?: 'the SSC GCash account' }}</strong>.</li>
                            <li>Add reference <span class="reference-code">{{ $payment?->reference ?? 'generated after submission' }}</span> in the message field when available.</li>
                            <li>Save the successful transaction receipt and upload it below.</li>
                        </ol>
                    </div>

                    <div id="instructions_instapay" class="instruction-panel mb-4" style="display:none;">
                        <div class="fw-bold text-dark mb-2"><i class="bi bi-bank2 text-success me-2"></i>InstaPay transfer details</div>
                        <div class="row g-2 bg-white border rounded-3 p-3 mb-3">
                            <div class="col-5 text-muted">Bank</div><div class="col-7 fw-bold">{{ config('ssc.bank_name') }}</div>
                            <div class="col-5 text-muted">Account name</div><div class="col-7 fw-bold">{{ config('ssc.bank_account_name') }}</div>
                            <div class="col-5 text-muted">Account number</div><div class="col-7 fw-bold text-success font-monospace">{{ config('ssc.bank_account_number') }}</div>
                        </div>
                        <ol>
                            <li>Transfer exactly <strong>{{ \App\Helpers\SscHelper::formatCurrency($amount) }}</strong> through InstaPay.</li>
                            <li>Use reference <span class="reference-code">{{ $payment?->reference ?? 'generated after submission' }}</span> in the memo when available.</li>
                            <li>Save the completed transfer receipt and upload it below.</li>
                        </ol>
                    </div>

                    @if($payment?->proof_path)
                        <div class="alert alert-info"><i class="bi bi-file-earmark-check me-2"></i>Current receipt status: <strong>{{ ucfirst($payment->proof_status ?? 'pending') }}</strong>@if($payment->proof_notes)<div class="small mt-1">SSC note: {{ $payment->proof_notes }}</div>@endif</div>
                    @endif

                    <div class="mb-4">
                        <label for="payment_proof" class="form-label fw-bold">Payment receipt <span class="text-danger">*</span></label>
                        <input id="payment_proof" type="file" name="proof" accept="image/jpeg,image/png,application/pdf,video/mp4" class="form-control form-control-lg" required>
                        <div class="form-text">JPG, PNG, PDF, or MP4 ? Maximum 5 MB</div>
                    </div>

                    <button type="submit" class="btn btn-dark px-4 py-3 rounded-3 fw-bold"><i class="bi bi-cloud-arrow-up-fill me-2"></i>Submit receipt for review</button>
                </form>
            </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const radios = document.querySelectorAll('input[name="payment_method"]');
        const gcash = document.getElementById('instructions_gcash');
        const instapay = document.getElementById('instructions_instapay');

        function showInstructions(method) {
            if (!gcash || !instapay) return;
            gcash.style.display = method === 'gcash' ? 'block' : 'none';
            instapay.style.display = method === 'instapay' ? 'block' : 'none';
        }

        radios.forEach(function (radio) {
            radio.addEventListener('change', function () { showInstructions(this.value); });
            if (radio.checked) showInstructions(radio.value);
        });

        document.querySelectorAll('.paymongo-form').forEach(function (form) {
            form.addEventListener('submit', function () {
                const button = form.querySelector('button');
                button.disabled = true;
                button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Opening secure checkout?';
            });
        });
    });
</script>
@endsection
