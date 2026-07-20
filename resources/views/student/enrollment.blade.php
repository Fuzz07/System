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
                <div class="alert alert-success">You have already paid for this semester. Reference: {{ $payment->reference }}</div>
            @else
                <div class="alert alert-warning">{{ $payment ? 'You have a pending payment. Reference: ' . $payment->reference : 'You do not have a payment record yet.' }}</div>
                <div class="mb-3">
                    <h6>GCash Instructions</h6>
                    <ol>
                        <li>Open GCash and send {{ $amount }} to <strong>{{ config('ssc.gcash_number') ?: 'GCASH_NUMBER' }}</strong>.</li>
                        <li>Enter the reference: <strong>{{ $payment->reference ?? 'will be generated' }}</strong> in the message/notes.</li>
                        <li>After payment, upload a screenshot or receipt using the form below so admin can verify it.</li>
                    </ol>
                </div>

                @if($payment && $payment->proof_path)
                    <div class="alert alert-info">
                        Proof status: <strong>{{ ucfirst($payment->proof_status ?? 'pending') }}</strong>
                        @if($payment->proof_notes)
                            <div>{{ $payment->proof_notes }}</div>
                        @endif
                    </div>
                @endif

                <form method="POST" action="{{ route('student.enrollment.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Upload GCash payment proof</label>
                        <input type="file" name="proof" accept="image/*,.pdf,.mp4" class="form-control" />
                        <div class="form-text">Optional but recommended. JPG, PNG, PDF, MP4 up to 5MB.</div>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit Proof / Create Payment Record</button>
                </form>

                @if(! $payment)
                    <small class="text-muted d-block mt-2">Pressing the button will create a payment record and show GCash instructions.</small>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
