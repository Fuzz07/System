@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-student') @endsection

@section('content')
<div class="page-header"><div><h1>SSC Officers Profiles</h1><p>Meet the dedicated leaders of your Supreme Student Council</p></div></div>

<div class="row g-4">
    @forelse($officers as $officer)
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 transition hover-shadow border-0 shadow-sm text-center p-4" style="border-radius:20px;">
            <div class="mb-4 d-flex justify-content-center">
                @if(!empty($officer->profile_pic))
                <img src="{{ asset('assets/img/' . $officer->profile_pic) }}" alt="{{ $officer->fullname }}" class="rounded-circle object-fit-cover shadow-sm" style="width:100px;height:100px;border:4px solid var(--primary-100);">
                @else
                <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width:100px;height:100px;font-size:2.5rem;font-weight:700;background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:white;border:4px solid var(--primary-100);">{{ strtoupper(substr($officer->fullname, 0, 1)) }}</div>
                @endif
            </div>
            <h3 class="h5 fw-bold text-dark mb-1">{{ $officer->fullname }}</h3>
            <div class="mb-2">
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-1.5" style="border-radius:10px;">
                    {{ $officer->position ?? ucfirst($officer->role) }}
                </span>
            </div>
            @if(!empty($officer->party))
            <div class="small text-muted mb-2"><i class="bi bi-flag text-danger"></i> {{ $officer->party }}</div>
            @endif
            <div class="text-muted small mb-3"><i class="bi bi-building"></i> {{ $officer->department ?: 'SSC General' }}</div>
            <div class="mt-auto"><a href="mailto:{{ $officer->email }}" class="btn btn-sm btn-outline-primary w-100" style="border-radius:10px;font-weight:600;"><i class="bi bi-envelope"></i> Contact</a></div>
        </div>
    </div>
    @empty
    <div class="col-12"><div class="card"><div class="card-body-custom text-center text-muted py-5"><i class="bi bi-people" style="font-size:3rem;opacity:.2;"></i><p class="mt-3">No officer profiles available.</p></div></div></div>
    @endforelse
</div>
@endsection

@section('chatbot') @include('partials.chatbot') @endsection
