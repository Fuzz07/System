@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-student') @endsection

@section('content')
<div class="page-header">
    <div>
        <h1>Ballot & Voting Portal</h1>
        <p>Your secure, time-limited student council voting session</p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card border-0 shadow-sm p-4 text-center" style="border-radius: 24px; background: #ffffff;">
            <div class="mb-3" style="font-size: 3.5rem;">⏱️</div>
            <h2 class="fw-bold text-dark mb-1">Position-by-Position Voting</h2>
            <h4 class="text-primary fw-bold mb-3">Up Next: {{ $currentPosition }}</h4>
            
            <div class="p-3 mb-4 rounded-4 bg-warning bg-opacity-10 border border-warning border-opacity-25 text-start">
                <h6 class="fw-bold text-warning-800 mb-2"><i class="bi bi-shield-lock-fill"></i> Strict Security Rules:</h6>
                <ul class="text-muted small mb-0 ps-3" style="line-height: 1.6;">
                    <li>Once you start, you have exactly <strong>1 minute (60 seconds)</strong> to review the candidates and cast your vote.</li>
                    <li>If the timer runs out before you submit, your ballot for this position will <strong>expire</strong> and cannot be reset.</li>
                    <li>Leaving or refreshing the page will <strong>not</strong> reset the timer.</li>
                    <li>Your IP Address <strong>({{ request()->ip() }})</strong> and browser fingerprint will be securely logged for audit purposes.</li>
                </ul>
            </div>

            <form method="POST" action="{{ route('student.voting.start') }}">
                @csrf
                <input type="hidden" name="position" value="{{ $currentPosition }}">
                <button type="submit" class="btn btn-primary w-100 py-3 rounded-4 fw-bold shadow-sm" style="font-size:1.05rem;">
                    <i class="bi bi-clock-play"></i> Start 1-Minute Timer & Show Candidates
                </button>
            </form>

            <form method="POST" action="{{ route('student.voting.skip') }}" class="mt-3">
                @csrf
                <input type="hidden" name="position" value="{{ $currentPosition }}">
                <button type="submit" class="btn btn-link btn-sm text-decoration-none text-muted" onsubmit="return confirm('Skip this position without casting a vote?')">
                    Skip this position
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
