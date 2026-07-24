@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-student') @endsection

@section('content')
<div class="page-header"><div><h1>SSC Officers Profiles</h1><p>Meet the dedicated leaders of your Supreme Student Council</p></div></div>

<div class="row g-4">
    @forelse($officers as $officer)
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 transition hover-shadow border-0 shadow-sm text-center p-4" style="border-radius:20px;">
            <div class="mb-4 d-flex justify-content-center">
                <div style="cursor:pointer;" data-bs-toggle="modal" data-bs-target="#officerModal{{ $officer->id }}" title="Click to view full profile">
                    @if(!empty($officer->profile_pic))
                    <img src="{{ asset('assets/img/' . $officer->profile_pic) }}" alt="{{ $officer->fullname }}" class="rounded-circle object-fit-cover shadow-sm hover-scale" style="width:100px;height:100px;border:4px solid var(--primary-100);transition:transform 0.2s;">
                    @else
                    <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm hover-scale" style="width:100px;height:100px;font-size:2.5rem;font-weight:700;background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:white;border:4px solid var(--primary-100);transition:transform 0.2s;">{{ strtoupper(substr($officer->fullname, 0, 1)) }}</div>
                    @endif
                </div>
            </div>
            <h3 class="h5 fw-bold text-dark mb-1" style="cursor:pointer;" data-bs-toggle="modal" data-bs-target="#officerModal{{ $officer->id }}">{{ $officer->fullname }}</h3>
            <div class="mb-2">
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-1.5" style="border-radius:10px;">
                    {{ $officer->position ?? ucfirst($officer->role) }}
                </span>
            </div>
            @if(!empty($officer->party))
            <div class="small text-muted mb-2"><i class="bi bi-flag text-danger"></i> {{ $officer->party }}</div>
            @endif
            <div class="text-muted small mb-3"><i class="bi bi-building"></i> {{ $officer->department ?: 'SSC General' }}</div>
            <div class="mt-auto">
                <button type="button" class="btn btn-sm btn-outline-primary w-100" style="border-radius:10px;font-weight:600;" data-bs-toggle="modal" data-bs-target="#officerModal{{ $officer->id }}">
                    <i class="bi bi-person-lines-fill"></i> View Profile
                </button>
            </div>
        </div>
    </div>

    {{-- Officer Profile Modal --}}
    <div class="modal fade" id="officerModal{{ $officer->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:24px; border:none; box-shadow:0 15px 35px rgba(15, 23, 42, 0.15); overflow:hidden;">
                <!-- Modal Header Banner -->
                <div style="height:120px; background:linear-gradient(135deg, #4f46e5, #312e81); position:relative;">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" style="position:absolute; top:20px; right:20px; filter:invert(1); z-index:10; opacity:0.8;"></button>
                </div>
                <!-- Profile Avatar Overlap -->
                <div class="text-center" style="margin-top:-60px; position:relative; z-index:5;">
                    @if(!empty($officer->profile_pic))
                    <img src="{{ asset('assets/img/' . $officer->profile_pic) }}" alt="{{ $officer->fullname }}" class="rounded-circle shadow" style="width:120px; height:120px; border:5px solid white; object-fit:cover;">
                    @else
                    <div class="rounded-circle d-flex align-items-center justify-content-center shadow mx-auto" style="width:120px; height:120px; font-size:3rem; font-weight:700; background:linear-gradient(135deg, #6366f1, #4f46e5); color:white; border:5px solid white;">{{ strtoupper(substr($officer->fullname, 0, 1)) }}</div>
                    @endif
                </div>
                <!-- Modal Body -->
                <div class="modal-body px-5 pb-5 pt-3 text-center">
                    <h4 style="font-weight:800; color:#0f172a; margin-bottom:4px;">{{ $officer->fullname }}</h4>
                    <p class="text-muted small mb-3">Joined since {{ $officer->created_at?->format('M Y') ?: 'General Session' }}</p>

                    <div class="mb-4">
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-1.5 fw-bold" style="border-radius:12px; font-size:0.8rem;">
                            {{ $officer->position ?? ucfirst($officer->role) }}
                        </span>
                    </div>

                    <hr class="my-4" style="border-color:#e2e8f0;">

                    <!-- Officer Info Grid -->
                    <div class="text-start">
                        <div class="row g-3">
                            @if(!empty($officer->party))
                            <div class="col-12 d-flex align-items-center gap-3">
                                <div class="d-flex align-items-center justify-content-center bg-danger bg-opacity-10 text-danger rounded-3" style="width:38px; height:38px; font-size:1.1rem;"><i class="bi bi-flag-fill"></i></div>
                                <div>
                                    <div class="text-muted" style="font-size:0.72rem; text-transform:uppercase; font-weight:700; letter-spacing:0.5px;">Political Party</div>
                                    <div style="font-weight:600; color:#334155; font-size:0.92rem;">{{ $officer->party }}</div>
                                </div>
                            </div>
                            @endif

                            <div class="col-12 d-flex align-items-center gap-3">
                                <div class="d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-3" style="width:38px; height:38px; font-size:1.1rem;"><i class="bi bi-building"></i></div>
                                <div>
                                    <div class="text-muted" style="font-size:0.72rem; text-transform:uppercase; font-weight:700; letter-spacing:0.5px;">Department</div>
                                    <div style="font-weight:600; color:#334155; font-size:0.92rem;">{{ $officer->department ?: 'SSC General Council' }}</div>
                                </div>
                            </div>

                            <div class="col-12 d-flex align-items-center gap-3">
                                <div class="d-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success rounded-3" style="width:38px; height:38px; font-size:1.1rem;"><i class="bi bi-calendar2-check"></i></div>
                                <div>
                                    <div class="text-muted" style="font-size:0.72rem; text-transform:uppercase; font-weight:700; letter-spacing:0.5px;">Year Level</div>
                                    <div style="font-weight:600; color:#334155; font-size:0.92rem;">{{ $officer->year_level ?: '—' }}</div>
                                </div>
                            </div>

                            <div class="col-12 d-flex align-items-center gap-3">
                                <div class="d-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning rounded-3" style="width:38px; height:38px; font-size:1.1rem;"><i class="bi bi-person-badge"></i></div>
                                <div>
                                    <div class="text-muted" style="font-size:0.72rem; text-transform:uppercase; font-weight:700; letter-spacing:0.5px;">Age & Council Standing</div>
                                    <div style="font-weight:600; color:#334155; font-size:0.92rem;">{{ $officer->age }} Years Old &bull; {{ ucfirst($officer->status) }}</div>
                                </div>
                            </div>

                            <div class="col-12 d-flex align-items-center gap-3">
                                <div class="d-flex align-items-center justify-content-center bg-info bg-opacity-10 text-info rounded-3" style="width:38px; height:38px; font-size:1.1rem;"><i class="bi bi-envelope"></i></div>
                                <div>
                                    <div class="text-muted" style="font-size:0.72rem; text-transform:uppercase; font-weight:700; letter-spacing:0.5px;">Microsoft 365 Account</div>
                                    <div style="font-weight:600; color:#334155; font-size:0.92rem; word-break: break-all;">{{ $officer->email }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-2">
                        <a href="mailto:{{ $officer->email }}" class="btn btn-primary w-100 py-2.5" style="border-radius:12px; font-weight:700; background:linear-gradient(135deg, #4f46e5, #4338ca); border:none;"><i class="bi bi-chat-left-text-fill"></i> Send Email Message</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12"><div class="card"><div class="card-body-custom text-center text-muted py-5"><i class="bi bi-people" style="font-size:3rem;opacity:.2;"></i><p class="mt-3">No officer profiles available.</p></div></div></div>
    @endforelse
</div>
@endsection

@section('chatbot') @include('partials.chatbot') @endsection
