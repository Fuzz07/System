@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-student') @endsection

@section('content')
<div class="page-header">
    <div>
        <h1>Ballot & Voting Portal</h1>
        <p>Participate in the SSC elections and choose your student council representatives</p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-8 text-center py-5">
        <div class="card border-0 shadow-sm p-5" style="border-radius: 24px; background: #ffffff;">
            <div class="mb-4" style="font-size: 4.5rem;">🗳️</div>
            <h2 class="fw-bold text-dark mb-3">Voting Portal is Currently Closed</h2>
            
            @if($activeSy && $activeSy->results_announced)
                <p class="text-muted mb-4 fs-6">The elections have officially concluded for School Year <strong>{{ $activeSy->label }}</strong>. The results have been published on the announcement board.</p>
                <a href="{{ route('student.election.results') }}" class="btn btn-primary px-4 py-2.5 fw-bold" style="border-radius: 10px;">
                    <i class="bi bi-bar-chart-fill me-1"></i> View Official Election Results
                </a>
            @else
                <p class="text-muted mb-4 fs-6">Elections are not active at this time. Admin has not announced the voting period for School Year <strong>{{ $activeSy->label ?? 'N/A' }}</strong> yet, or the voting window has ended. Please stay tuned for announcement board updates.</p>
                <a href="{{ route('student.overview') }}" class="btn btn-outline-primary px-4 py-2.5 fw-bold" style="border-radius: 10px;">
                    <i class="bi bi-arrow-left me-1"></i> Return to Dashboard
                </a>
            @endif
        </div>
    </div>
</div>
@endsection
