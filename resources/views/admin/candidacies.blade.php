@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-admin') @endsection

@section('content')
<div class="page-header">
    <div>
        <h1>SSC Candidacy Filings</h1>
        <p>Monitor student applications to run as SSC officers and check deans' selections across departments</p>
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
                    <p class="text-muted mb-0 small">Total Filings</p>
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
                    <p class="text-muted mb-0 small">Pending Review</p>
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
                    <p class="text-muted mb-0 small">Dean Voted / Selected</p>
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
                    <p class="text-muted mb-0 small">Declined filings</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Candidacy Table --}}
<div class="card border-0 shadow-sm" style="border-radius:20px; overflow:hidden;">
    <div class="card-header-custom bg-light p-4 border-0">
        <span class="card-title h5 mb-0 fw-bold" style="color:var(--navy-900);">Active Election Candidacies</span>
    </div>
    <div class="card-body-custom p-0">
        <div class="table-responsive">
            <table class="table-custom mb-0">
                <thead>
                    <tr>
                        <th>Candidate</th>
                        <th>Department</th>
                        <th>Position</th>
                        <th>Manifesto</th>
                        <th>Dean Endorsement Status</th>
                        <th>School Year</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($candidacies as $c)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar bg-light text-primary d-flex align-items-center justify-content-center" style="width:40px; height:40px; border-radius:10px; font-weight:700; font-size:0.9rem; background:rgba(79,70,229,0.08);">
                                    {{ $c->user->avatar }}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">{{ $c->user->fullname }}</div>
                                    <div class="text-muted small">ID: {{ $c->user->student_id }} · Yr: {{ $c->user->year_level }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-secondary" style="font-size:0.75rem; border-radius:6px; font-weight:600;">{{ $c->department }}</span>
                        </td>
                        <td>
                            <div style="font-weight:700; color:var(--navy-900);">{{ $c->position }}</div>
                        </td>
                        <td>
                            <div class="text-truncate text-muted small" style="max-width:200px; cursor:pointer;" title="Click to expand" data-bs-toggle="collapse" data-bs-target="#platformCollapse{{ $c->id }}">
                                <i class="bi bi-eye"></i> View manifesto
                            </div>
                            <div class="collapse mt-2 p-3 bg-light rounded-3 text-muted small" id="platformCollapse{{ $c->id }}" style="white-space:pre-wrap; line-height:1.5;">
                                {{ $c->platform }}
                            </div>
                        </td>
                        <td>
                            @if($c->status === 'pending')
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-2" style="border-radius:8px;">Pending Dean Review</span>
                            @elseif($c->status === 'approved')
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2" style="border-radius:8px;"><i class="bi bi-check-lg"></i> Voted / Selected</span>
                            @else
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2" style="border-radius:8px;">Declined</span>
                            @endif
                        </td>
                        <td style="font-family:monospace; font-size:0.85rem;">
                            {{ $c->school_year }}
                        </td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('admin.candidacy.destroy', $c) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this candidacy application?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm" style="border-radius:8px; font-size:0.75rem; padding:6px 12px;">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-inbox" style="font-size:3rem; opacity:0.2;"></i>
                            <p class="mt-3 mb-0">No candidacy applications submitted yet.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
