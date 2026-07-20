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
            <div class="m-alert m-alert-success">Your enrollment fee is marked as paid. Reference: {{ $payment->reference }}</div>
        @else
            <div class="m-alert m-alert-warning">{{ $payment ? 'Pending payment reference: ' . $payment->reference : 'No payment record found yet.' }}</div>
            <div class="m-card-section" style="margin-bottom: 16px;">
                <div style="font-weight: 700; margin-bottom: 8px;">GCash Instructions</div>
                <div style="font-size: 0.9rem; line-height: 1.6;">
                    <p>Send {{ \App\Helpers\SscHelper::formatCurrency($amount) }} to <strong>{{ config('ssc.gcash_number') ?: 'GCASH_NUMBER' }}</strong>.</p>
                    <p>Include the reference <strong>{{ $payment->reference ?? 'will be generated' }}</strong> in the GCash message.</p>
                    <p>After sending payment, upload proof below for admin verification.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('mobile.student.enrollment.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="m-field" style="margin-bottom: 14px;">
                    <label>Upload Payment Proof</label>
                    <input type="file" name="proof" accept="image/*,.pdf,.mp4" class="form-control" style="padding: 10px; border-radius: 12px; border: 1px solid #d1d5db; width: 100%;" />
                </div>
                <button type="submit" class="m-btn m-btn-primary" style="width: 100%;">Upload Proof / Create Record</button>
            </form>
        @endif

        @if($payment && $payment->proof_path)
            <div class="m-card-section" style="margin-top: 16px;">
                <div style="font-weight: 700; margin-bottom: 8px;">Proof Status</div>
                <div>{{ ucfirst($payment->proof_status ?? 'pending') }}</div>
                @if($payment->proof_notes)
                    <div class="m-field" style="margin-top: 8px;">Notes: {{ $payment->proof_notes }}</div>
                @endif
                <a href="{{ asset('storage/' . $payment->proof_path) }}" class="m-btn m-btn-secondary" style="margin-top: 10px; display: inline-block;">View Proof</a>
            </div>
        @endif
    @endif
</div>
@endsection
