@extends('layouts.app')

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
                <div class="alert alert-success">You have already paid for this semester. Reference: {{ $payment->reference }}</div>
            @elseif($payment && $payment->status === 'pending')
                <div class="alert alert-warning">You have a pending payment. Reference: <strong>{{ $payment->reference }}</strong></div>
                <div class="mb-3">
                    <h6>GCash Instructions</h6>
                    <ol>
                        <li>Open GCash and send {{ $amount }} to <strong>{{ config('ssc.gcash_number') ?: 'GCASH_NUMBER' }}</strong>.</li>
                        <li>Enter the reference: <strong>{{ $payment->reference }}</strong> in the message/notes.</li>
                        <li>After payment, wait for confirmation. Admin will verify and mark paid for walk-ins or manual confirmations.</li>
                    </ol>
                </div>
            @else
                <form method="POST" action="{{ route('student.enrollment.store') }}">
                    @csrf
                    <button class="btn btn-primary">Pay via GCash (Create Payment Record)</button>
                </form>
                <small class="text-muted d-block mt-2">Pressing the button will create a payment record and show GCash instructions.</small>
            @endif
        </div>
    </div>
</div>
@endsection
