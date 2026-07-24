@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-student') @endsection
@section('sidebar-nav')
    <a href="{{ route('student.overview') }}" class="nav-item">
        <i class="bi bi-house-door"></i>
        <span>Overview</span>
    </a>
    <a href="{{ route('student.proposals') }}" class="nav-item">
        <i class="bi bi-file-text"></i>
        <span>Proposals</span>
    </a>
    <a href="{{ route('student.announcements') }}" class="nav-item">
        <i class="bi bi-megaphone"></i>
        <span>Announcements</span>
    </a>
    <a href="{{ route('student.officers') }}" class="nav-item">
        <i class="bi bi-people"></i>
        <span>Officers</span>
    </a>
    <a href="{{ route('student.feedback') }}" class="nav-item">
        <i class="bi bi-chat-dots"></i>
        <span>Feedback</span>
    </a>
    <a href="{{ route('student.voting') }}" class="nav-item">
        <i class="bi bi-ballot"></i>
        <span>Voting</span>
    </a>
    <a href="{{ route('student.candidacy') }}" class="nav-item">
        <i class="bi bi-award"></i>
        <span>Candidacy</span>
    </a>
    <a href="{{ route('student.enrollment.index') }}" class="nav-item">
        <i class="bi bi-cash-stack"></i>
        <span>Enrollment</span>
    </a>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h4>Enrollment Fee</h4>
            <p class="text-muted">Amount: {{ \App\Helpers\SscHelper::formatCurrency($amount) }}</p>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if($payment && $payment->status === 'paid')
                <div class="alert alert-success d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill" style="font-size: 1.25rem;"></i>
                    <span>You have already paid for this semester. Method: {{ $payment->method === 'instapay' ? 'InstaPay' : 'GCash' }} | Reference: {{ $payment->reference }}</span>
                </div>
            @else
                <div class="alert alert-warning d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle-fill" style="font-size: 1.25rem;"></i>
                    <span>{{ $payment ? 'You have a pending payment ('. ($payment->method === 'instapay' ? 'InstaPay' : 'GCash') .'). Reference: ' . $payment->reference : 'You do not have a payment record yet.' }}</span>
                </div>

                <form method="POST" action="{{ route('student.enrollment.store') }}" enctype="multipart/form-data">
                    @csrf

                    @if(! $payment)
                        <!-- Select Payment Method -->
                        <div class="mb-4 bg-light p-3 rounded-3" style="border: 1px solid #e2e8f0;">
                            <label class="form-label fw-bold d-block mb-3 text-secondary" style="font-size:0.9rem;">SELECT PAYMENT METHOD</label>
                            <div class="d-flex gap-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="method_gcash" value="gcash" checked onchange="toggleMethodInstructions('gcash')">
                                    <label class="form-check-label fw-semibold text-slate-800" for="method_gcash" style="cursor:pointer; user-select:none;">
                                        <i class="bi bi-wallet2 text-primary" style="font-size:1.15rem; vertical-align:middle; margin-right:4px;"></i> GCash Wallet
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="method_instapay" value="instapay" onchange="toggleMethodInstructions('instapay')">
                                    <label class="form-check-label fw-semibold text-slate-800" for="method_instapay" style="cursor:pointer; user-select:none;">
                                        <i class="bi bi-bank text-success" style="font-size:1.1rem; vertical-align:middle; margin-right:4px;"></i> InstaPay / Bank Transfer
                                    </label>
                                </div>
                            </div>
                        </div>
                    @else
                        <input type="hidden" name="payment_method" value="{{ $payment->method }}" />
                    @endif

                    <!-- GCash Instructions Panel -->
                    <div id="instructions_gcash" class="p-4 rounded-4 mb-4" style="background: rgba(37, 99, 235, 0.04); border: 1.5px solid rgba(37, 99, 235, 0.15); display: {{ (!$payment || $payment->method === 'gcash') ? 'block' : 'none' }};">
                        <h6 class="fw-bold text-primary mb-3 d-flex align-items-center gap-2" style="font-size:1.05rem;">
                            <i class="bi bi-phone-vibrate-fill"></i> GCash Payment Instructions
                        </h6>
                        <ol class="mb-0 text-slate-700" style="font-size:0.92rem; line-height:1.75; padding-left:20px;">
                            <li>Open your **GCash App** and choose **Send Money** &rarr; **Express Send**.</li>
                            <li>Send exactly **{{ \App\Helpers\SscHelper::formatCurrency($amount) }}** to the Admin's registered GCash Number: <strong class="text-primary">{{ config('ssc.gcash_number') ?: 'GCASH_NUMBER' }}</strong></li>
                            <li>Enter the generated reference code: <strong class="text-dark bg-warning px-2 py-0.5 rounded-2 fw-bold" style="font-size:0.85rem; font-family:monospace;">{{ $payment->reference ?? 'will be generated' }}</strong> inside the GCash **Message/Notes** field.</li>
                            <li>After completing the transfer, take a screenshot of your official receipt and upload it in the form below.</li>
                        </ol>

                        <!-- Open GCash App Deep Link Button (Visible on Mobile Only) -->
                        <div class="mt-3 d-md-none">
                            <a href="javascript:void(0)" onclick="openGcash()" class="btn btn-primary d-inline-flex align-items-center gap-2" style="border-radius:10px; padding:10px 20px; font-weight:700; background:#0055ff; border:none; box-shadow: 0 4px 12px rgba(0, 85, 255, 0.25);">
                                <i class="bi bi-box-arrow-up-right"></i> Open GCash App
                            </a>
                        </div>
                    </div>

                    <!-- InstaPay / Banking Instructions Panel -->
                    <div id="instructions_instapay" class="p-4 rounded-4 mb-4" style="background: rgba(16, 185, 129, 0.04); border: 1.5px solid rgba(16, 185, 129, 0.15); display: {{ ($payment && $payment->method === 'instapay') ? 'block' : 'none' }};">
                        <h6 class="fw-bold text-success mb-3 d-flex align-items-center gap-2" style="font-size:1.05rem;">
                            <i class="bi bi-bank2"></i> InstaPay / Bank Transfer Instructions
                        </h6>
                        <ol class="mb-4 text-slate-700" style="font-size:0.92rem; line-height:1.75; padding-left:20px;">
                            <li>Open your banking app (BDO, BPI, Landbank, GCash, PayMaya, etc.) and choose **Transfer / Send Money to other Banks** via **InstaPay**.</li>
                            <li>Enter the Admin's Official Bank Account Details:</li>
                        </ol>
                        
                        <div class="row g-2 mb-3 bg-white p-3 rounded-3 shadow-xs border" style="max-width:480px; font-size:0.9rem; margin-left:10px;">
                            <div class="col-4 text-muted fw-semibold">Bank Name:</div>
                            <div class="col-8 fw-bold text-slate-800">{{ config('ssc.bank_name') }}</div>
                            <div class="col-4 text-muted fw-semibold">Account Name:</div>
                            <div class="col-8 fw-bold text-slate-800">{{ config('ssc.bank_account_name') }}</div>
                            <div class="col-4 text-muted fw-semibold">Account Number:</div>
                            <div class="col-8 fw-bold text-success font-monospace" style="letter-spacing:1px; font-size:0.95rem;">{{ config('ssc.bank_account_number') }}</div>
                        </div>

                        <ol start="3" class="mb-0 text-slate-700" style="font-size:0.92rem; line-height:1.75; padding-left:20px;">
                            <li>Send exactly **{{ \App\Helpers\SscHelper::formatCurrency($amount) }}** and enter the reference code: <strong class="text-dark bg-warning px-2 py-0.5 rounded-2 fw-bold" style="font-size:0.85rem; font-family:monospace;">{{ $payment->reference ?? 'will be generated' }}</strong> inside your bank's description/memo box if available.</li>
                            <li>Save or screenshot the completed InstaPay transfer confirmation, and upload it below for verification.</li>
                        </ol>
                    </div>

                    @if($payment && $payment->proof_path)
                        <div class="alert alert-info d-flex align-items-center gap-2">
                            <i class="bi bi-file-earmark-image" style="font-size:1.25rem;"></i>
                            <div>
                                Proof status: <strong>{{ ucfirst($payment->proof_status ?? 'pending') }}</strong>
                                @if($payment->proof_notes)
                                    <div class="mt-1 small">Admin Notes: {{ $payment->proof_notes }}</div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="mb-4">
                        <label class="form-label fw-bold">Upload Payment Receipt / Proof</label>
                        <input type="file" name="proof" accept="image/*,.pdf,.mp4" class="form-control" style="border-radius:10px;" />
                        <div class="form-text">Supported formats: JPG, PNG, PDF, MP4 up to 5MB. Screenshots must clearly display the transfer amount and transaction date.</div>
                    </div>

                    <button type="submit" class="btn-primary-custom" style="padding:14px 28px; border-radius:12px; font-weight:700;">
                        <i class="bi bi-cloud-arrow-up-fill"></i> Submit Proof / Create Payment Record
                    </button>
                </form>

                @if(! $payment)
                    <small class="text-muted d-block mt-3"><i class="bi bi-shield-fill-exclamation text-secondary"></i> Clicking the button above will generate your unique reference code and register your payment entry.</small>
                @endif
            @endif
        </div>
    </div>
</div>

<script>
    function toggleMethodInstructions(method) {
        const gcashPanel = document.getElementById('instructions_gcash');
        const instapayPanel = document.getElementById('instructions_instapay');
        if (method === 'instapay') {
            gcashPanel.style.display = 'none';
            instapayPanel.style.display = 'block';
        } else {
            gcashPanel.style.display = 'block';
            instapayPanel.style.display = 'none';
        }
    }

    function openGcash() {
        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
        if (isIOS) {
            window.location.href = 'gcash://';
        } else {
            // High-speed Android Intent directly invoking Package Manager
            window.location.href = 'intent://#Intent;scheme=gcash;package=com.globe.gcash.android;end';
        }
    }
</script>
@endsection
