@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-student') @endsection

@section('content')
<div class="page-header">
    <div>
        <h1>Ballot Completed</h1>
        <p>Thank you for casting your votes and participating in the SSC election process</p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm p-5 text-center mb-4" style="border-radius: 24px; background: #ffffff;">
            <div class="mb-3" style="font-size: 4rem;">🎉</div>
            <h2 class="fw-bold text-dark mb-2">Thank You, {{ Auth::user()->first_name }}!</h2>
            <p class="text-muted mb-4 fs-6">Your student council election ballot has been successfully received, finalized, and locked. You can no longer make modifications.</p>
            
            <hr class="my-4" style="opacity:0.1;">
            
            <h5 class="fw-bold text-dark mb-4 text-start"><i class="bi bi-journal-check me-1"></i> Your Cast Ballot Summary:</h5>
            <div class="d-flex flex-column gap-3 text-start">
                @foreach($positionsWithCandidates as $pos)
                    @php
                        $myVote = $myVotes->where('position', $pos)->first();
                    @endphp
                    <div class="p-3 rounded-4 d-flex align-items-center justify-content-between" style="background: #f8fafc; border: 1px solid #f1f5f9;">
                        <div>
                            <div class="fw-bold text-dark" style="font-size:0.95rem;">{{ $pos }}</div>
                            @if($myVote)
                                <div class="text-success small fw-semibold mt-0.5"><i class="bi bi-check2"></i> Selected: {{ $myVote->candidacy->user->fullname }}</div>
                            @else
                                <div class="text-danger small fw-semibold mt-0.5"><i class="bi bi-clock-history"></i> Skipped / Expired</div>
                            @endif
                        </div>
                        <div>
                            @if($myVote)
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-10 px-3 py-1.5 rounded-pill">Cast</span>
                            @else
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-10 px-3 py-1.5 rounded-pill">No Vote</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-5 d-flex justify-content-center">
                <a href="{{ route('student.election.results') }}" class="btn btn-primary px-4 py-2.5 fw-bold shadow-sm" style="border-radius:10px;">
                    <i class="bi bi-bar-chart-fill me-1"></i> Election Results
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
