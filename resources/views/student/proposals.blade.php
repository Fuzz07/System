@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-student') @endsection

@section('content')
<div class="page-header"><div><h1>Active SSC Projects</h1><p>Approved initiatives open for student discussion and feedback</p></div></div>

<div class="row g-4">
    @forelse($proposals as $p)
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 transition hover-shadow border-0 shadow-sm" style="border-radius:20px;overflow:hidden;">
            <div class="p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="d-flex gap-2">
                        @if($p->status === 'Approved')<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2" style="border-radius:10px;">Approved</span>
                        @else <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-2" style="border-radius:10px;">Pending Review</span>@endif
                        @if($p->project_status === 'Completed')<span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3 py-2" style="border-radius:10px;"><i class="bi bi-check2-circle"></i> Done</span>@endif
                    </div>
                    <div class="text-muted small"><i class="bi bi-chat-text"></i> {{ $p->comments_count }}</div>
                </div>
                <h2 class="h5 fw-bold text-dark mb-2" style="line-height:1.4;">{{ $p->project_title }}</h2>
                <p class="text-muted small mb-4" style="line-height:1.6;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;">{{ $p->description }}</p>
                <div class="d-flex align-items-center gap-3 mb-4 p-3 bg-light rounded-3">
                    <div class="avatar-sm bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center rounded-circle" style="width:36px;height:36px;font-weight:700;">{{ strtoupper(substr($p->officer->fullname ?? 'N', 0, 1)) }}</div>
                    <div><div class="fw-bold small">{{ $p->officer->fullname ?? 'N/A' }}</div><div class="text-muted" style="font-size:0.7rem;">Project Lead</div></div>
                </div>
                <a href="{{ route('student.proposal.show', $p) }}" class="btn-primary-custom w-100 justify-content-center py-2" style="border-radius:12px;">View Project & Discuss <i class="bi bi-arrow-right ms-2"></i></a>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12"><div class="card"><div class="card-body-custom text-center text-muted py-5"><i class="bi bi-folder2-open" style="font-size:3rem;opacity:.2;"></i><p class="mt-3">No approved proposals available for viewing at this time.</p></div></div></div>
    @endforelse
</div>
@endsection

@section('chatbot') @include('partials.chatbot') @endsection
