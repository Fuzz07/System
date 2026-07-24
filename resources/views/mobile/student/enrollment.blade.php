@php
    $pageTitle = 'Enrollment';
@endphp
@extends('layouts.mobile-student', ['pageTitle' => $pageTitle, 'showBack' => true, 'backUrl' => route('mobile.student.proposals')])

@section('content')
<div class="m-card elevated" style="margin-top: 16px;">
    <div class="m-card-header">
        <div>
            <div class="m-card-title">Enrollment Fee</div>
            <div class="m-card-sub">Pay your enrollment fee and upload proof for verification.</div>
        </div>
    </div>

    @if(session('success'))
        <div class="m-alert m-alert-success">{{ session('success') }}</div>
    @endif
    @if(session('info'))
        <div class="m-alert m-alert-warning">{{ session('info') }}</div>
    @endif

    <div class="m-card-body">
        <div class="m-field">
            <label>Amount</label>
            <div class="m-field-value">{{ \App\Helpers\SscHelper::formatCurrency($amount) }}</div>
        </div>

        @if($payment && $payment->status === 'paid')
            <div class="m-alert m-alert-success">Your enrollment fee is marked as paid. Method: {{ $payment->method === 'instapay' ? 'InstaPay' : 'GCash' }} | Reference: {{ $payment->reference }}</div>
        @else
            <div class="m-alert m-alert-warning">{{ $payment ? 'Pending payment ('. ($payment->method === 'instapay' ? 'InstaPay' : 'GCash') .') reference: ' . $payment->reference : 'No payment record found yet.' }}</div>
            
            <form method="POST" action="{{ route('mobile.student.enrollment.store') }}" enctype="multipart/form-data">
                @csrf

                @if(! $payment)
                    <!-- Select Payment Method -->
                    <div class="m-field" style="margin-bottom: 16px;">
                        <label>Select Payment Method</label>
                        <select name="payment_method" id="mobile_method_select" style="padding: 12px; border-radius: 12px; border: 1px solid #d1d5db; width: 100%; font-weight:600; background-color:#fff;" onchange="toggleMobileInstructions(this.value)">
                            <option value="gcash">GCash Wallet</option>
                            <option value="instapay">InstaPay / Bank Transfer</option>
                        </select>
                    </div>
                @else
                    <input type="hidden" name="payment_method" value="{{ $payment->method }}" />
                @endif

                <!-- GCash Instructions Mobile Panel -->
                <div id="mobile_instructions_gcash" class="m-card-section" style="margin-bottom: 20px; background: rgba(37, 99, 235, 0.03); border: 1px solid rgba(37, 99, 235, 0.1); border-radius:12px; padding:16px; display: {{ (!$payment || $payment->method === 'gcash') ? 'block' : 'none' }};">
                    <div style="font-weight: 700; margin-bottom: 10px; color:var(--primary);"><i class="bi bi-wallet2"></i> GCash Instructions</div>
                    <div style="font-size: 0.88rem; line-height: 1.6; color:#4b5563;">
                        <p style="margin-bottom:6px;">1. Send exactly <strong>{{ \App\Helpers\SscHelper::formatCurrency($amount) }}</strong> to the Admin's GCash number: <strong style="color:#111827;">{{ config('ssc.gcash_number') ?: 'GCASH_NUMBER' }}</strong></p>
                        <p style="margin-bottom:6px;">2. Enter this reference: <strong style="background:#fef08a; padding:2px 6px; border-radius:4px; font-family:monospace; color:#111827;">{{ $payment->reference ?? 'will be generated' }}</strong> inside the GCash message box.</p>
                        <p style="margin-bottom:12px;">3. Take a screenshot of the GCash receipt and upload it below.</p>
                    </div>

                    <!-- Open GCash App Deep Link Button -->
                    <div style="margin-top: 12px;">
                        <a href="gcash://" class="m-btn m-btn-primary" style="display:inline-flex; align-items:center; gap:8px; justify-content:center; background:#0055ff; border:none; padding:10px 20px; border-radius:12px; font-weight:700; font-size:0.85rem; color:#fff; text-decoration:none;">
                            <i class="bi bi-box-arrow-up-right"></i> Open GCash App
                        </a>
                    </div>
                </div>

                <!-- InstaPay Instructions Mobile Panel -->
                <div id="mobile_instructions_instapay" class="m-card-section" style="margin-bottom: 20px; background: rgba(16, 185, 129, 0.03); border: 1px solid rgba(16, 185, 129, 0.1); border-radius:12px; padding:16px; display: {{ ($payment && $payment->method === 'instapay') ? 'block' : 'none' }};">
                    <div style="font-weight: 700; margin-bottom: 10px; color:#059669;"><i class="bi bi-bank"></i> InstaPay / Bank Transfer</div>
                    <div style="font-size: 0.88rem; line-height: 1.6; color:#4b5563;">
                        <p style="margin-bottom:8px;">1. Send exactly <strong>{{ \App\Helpers\SscHelper::formatCurrency($amount) }}</strong> via <strong>InstaPay</strong> to the Admin's official bank account:</p>
                        
                        <div style="background:#fff; border:1px solid #e5e7eb; border-radius:8px; padding:10px; margin-bottom:8px; font-size:0.85rem;">
                            <div style="margin-bottom:4px;"><span style="color:#6b7280; font-weight:500;">Bank Name:</span> <strong style="color:#111827;">{{ config('ssc.bank_name') }}</strong></div>
                            <div style="margin-bottom:4px;"><span style="color:#6b7280; font-weight:500;">Account Name:</span> <strong style="color:#111827;">{{ config('ssc.bank_account_name') }}</strong></div>
                            <div style="margin-bottom:0;"><span style="color:#6b7280; font-weight:500;">Account No:</span> <strong style="color:#059669; font-family:monospace; font-size:0.92rem;">{{ config('ssc.bank_account_number') }}</strong></div>
                        </div>

                        <p style="margin-bottom:6px;">2. Put this reference: <strong style="background:#fef08a; padding:2px 6px; border-radius:4px; font-family:monospace; color:#111827;">{{ $payment->reference ?? 'will be generated' }}</strong> in the bank transfer memo/notes if available.</p>
                        <p style="margin-bottom:0;">3. Screenshot the transfer receipt and upload it below.</p>
                    </div>
                </div>

                <div class="m-field" style="margin-bottom: 16px;">
                    <label>Upload Payment Receipt / Proof</label>
                    <input type="file" name="proof" accept="image/*,.pdf,.mp4" class="form-control" style="padding: 10px; border-radius: 12px; border: 1px solid #d1d5db; width: 100%;" required />
                </div>
                <button type="submit" class="m-btn m-btn-primary" style="width: 100%;">Submit Proof / Create Record</button>
            </form>
        @endif

        @if($payment && $payment->proof_path)
            <div class="m-card-section" style="margin-top: 16px;">
                <div style="font-weight: 700; margin-bottom: 8px;">Proof Status</div>
                <div style="font-weight:600;">{{ ucfirst($payment->proof_status ?? 'pending') }}</div>
                @if($payment->proof_notes)
                    <div class="m-field" style="margin-top: 8px;">Notes: {{ $payment->proof_notes }}</div>
                @endif
                <a href="{{ asset('storage/' . $payment->proof_path) }}" class="m-btn m-btn-secondary" style="margin-top: 10px; display: inline-block;">View Proof</a>
            </div>
        @endif
    </div>
</div>

<script>
    function toggleMobileInstructions(method) {
        const gcash = document.getElementById('mobile_instructions_gcash');
        const instapay = document.getElementById('mobile_instructions_instapay');
        if (method === 'instapay') {
            gcash.style.display = 'none';
            instapay.style.display = 'block';
        } else {
            gcash.style.display = 'block';
            instapay.style.display = 'none';
        }
    }
</script>
@endsection
