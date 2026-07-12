@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-dean') @endsection

@section('content')
<div class="page-header">
    <div>
        <h1>Dean Endorsement Portal</h1>
        <p>Review candidacy filings, select, and vote for representatives of the <strong>{{ Auth::user()->department }}</strong> Department</p>
    </div>
    <div>
        <span class="badge bg-primary px-3 py-2" style="font-size:0.85rem; border-radius:10px;">
            Active SY: {{ $activeSy->label ?? 'N/A' }}
        </span>
    </div>
</div>

{{-- Stat Cards --}}
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="border-radius:16px;">
            <div class="card-body-custom p-4 d-flex align-items-center gap-3">
                <div style="width:48px;height:48px;background:rgba(79,70,229,0.1);color:var(--primary);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;"><i class="bi bi-people"></i></div>
                <div>
                    <h3 class="mb-1 fw-bold text-dark">{{ $stats['total'] }}</h3>
                    <p class="text-muted mb-0 small">Total Candidates</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="border-radius:16px;">
            <div class="card-body-custom p-4 d-flex align-items-center gap-3">
                <div style="width:48px;height:48px;background:rgba(245,158,11,0.1);color:var(--warning);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;"><i class="bi bi-hourglass-split"></i></div>
                <div>
                    <h3 class="mb-1 fw-bold text-dark">{{ $stats['pending'] }}</h3>
                    <p class="text-muted mb-0 small">Awaiting Review</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="border-radius:16px;">
            <div class="card-body-custom p-4 d-flex align-items-center gap-3">
                <div style="width:48px;height:48px;background:rgba(16,185,129,0.1);color:var(--success);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;"><i class="bi bi-check-circle"></i></div>
                <div>
                    <h3 class="mb-1 fw-bold text-dark">{{ $stats['approved'] }}</h3>
                    <p class="text-muted mb-0 small">Voted / Selected</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="border-radius:16px;">
            <div class="card-body-custom p-4 d-flex align-items-center gap-3">
                <div style="width:48px;height:48px;background:rgba(239,68,68,0.1);color:var(--danger);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;"><i class="bi bi-x-circle"></i></div>
                <div>
                    <h3 class="mb-1 fw-bold text-dark">{{ $stats['rejected'] }}</h3>
                    <p class="text-muted mb-0 small">Declined</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Candidacy Table --}}
<div class="card border-0 shadow-sm" style="border-radius:20px; overflow:hidden;">
    <div class="card-header-custom bg-light p-4 border-0">
        <span class="card-title h5 mb-0 fw-bold" style="color:var(--navy-900);">Student Filings</span>
    </div>
    <div class="card-body-custom p-0">
        <div class="table-responsive">
            <table class="table-custom mb-0">
                <thead>
                    <tr>
                        <th>Candidate Info</th>
                        <th>Target Position</th>
                        <th>Platform / Vision</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($candidacies as $c)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar bg-primary text-white d-flex align-items-center justify-content-center" style="width:42px; height:42px; border-radius:12px; font-weight:700;">
                                    {{ $c->user->avatar }}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">{{ $c->user->fullname }}</div>
                                    <div class="text-muted small">ID: {{ $c->user->student_id }} · {{ $c->user->year_level }} ({{ $c->user->department }})</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight:700;color:var(--navy-900);">{{ $c->position }}</div>
                        </td>
                        <td style="max-width:320px;">
                            <div class="text-truncate text-muted small" style="cursor:pointer;" title="Click to view full platform" data-bs-toggle="collapse" data-bs-target="#platformCollapse{{ $c->id }}">
                                <i class="bi bi-eye"></i> {{ Str::limit($c->platform, 80) }}
                            </div>
                            <div class="collapse mt-2 p-3 bg-light rounded-3 text-muted small" id="platformCollapse{{ $c->id }}" style="white-space:pre-wrap; line-height:1.5;">
                                <strong>Manifesto:</strong><br>{{ $c->platform }}
                            </div>
                        </td>
                        <td>
                            @if($c->status === 'pending')
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-2" style="border-radius:8px;">Awaiting Review</span>
                            @elseif($c->status === 'approved')
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2" style="border-radius:8px;"><i class="bi bi-check-lg"></i> Voted / Selected</span>
                            @else
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2" style="border-radius:8px;">Declined</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex gap-2 justify-content-end">
                                @if($c->status !== 'approved')
                                    <form method="POST" action="{{ route('dean.candidacy.vote', $c) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm" style="font-weight:700; border-radius:8px; font-size:0.75rem; padding:6px 12px;">
                                            <i class="bi bi-check-lg"></i> Approve & Vote
                                        </button>
                                    </form>
                                @endif
                                @if($c->status !== 'rejected')
                                    <form method="POST" action="{{ route('dean.candidacy.reject', $c) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-danger btn-sm" style="font-weight:700; border-radius:8px; font-size:0.75rem; padding:6px 12px;">
                                            <i class="bi bi-x-lg"></i> Decline
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5">
                            <i class="bi bi-inbox" style="font-size:3rem; opacity:0.2;"></i>
                            <p class="mt-3 mb-0">No candidacy applications submitted by students in your department yet.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
