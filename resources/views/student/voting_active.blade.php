@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-student') @endsection

@section('content')
<div class="page-header d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h1>Active Voting Session</h1>
        <p class="text-muted mb-0">Cast your vote for the position of <strong class="text-primary">{{ $currentPosition }}</strong></p>
    </div>
    
    {{-- Robust Header Timer Panel --}}
    <div class="p-3 bg-danger bg-opacity-10 border border-danger border-opacity-25 rounded-4 d-flex align-items-center gap-3" style="min-width: 200px;">
        <div style="font-size: 1.5rem; animation: pulse 1.5s infinite;"><i class="bi bi-clock-fill text-danger"></i></div>
        <div>
            <div class="small text-danger text-uppercase fw-semibold" style="font-size:0.7rem; letter-spacing:0.5px;">Remaining Time</div>
            <div class="h3 fw-bold text-danger mb-0" id="timer-display" style="font-variant-numeric: tabular-nums;">
                0:{{ str_pad($secondsRemaining, 2, '0', STR_PAD_LEFT) }}
            </div>
        </div>
    </div>
</div>

<div class="progress mb-4" style="height: 8px; border-radius: 99px; background-color: #f1f5f9;">
    <div id="timer-progress" class="progress-bar bg-danger" role="progressbar" style="width: {{ ($secondsRemaining / 60) * 100 }}%; border-radius: 99px; transition: width 1s linear;"></div>
</div>

<div class="row g-4">
    @forelse($candidates as $cand)
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100 transition-all hover-shadow" style="border-radius: 16px; border: 1px solid #f1f5f9;">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="avatar bg-primary text-white fw-bold d-flex align-items-center justify-content-center" style="width:50px; height:50px; border-radius:12px; font-size:1.2rem; background: rgba(79,70,229,0.08); color: var(--primary) !important;">
                                @if($cand->photo_url)
                                    <img src="{{ $cand->photo_url }}" alt="{{ $cand->user->fullname }}" style="width:100%; height:100%; border-radius:inherit; object-fit:cover; display:block;">
                                @else
                                    {{ $cand->user->avatar }}
                                @endif
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark mb-0">{{ $cand->user->fullname }}</h5>
                                <p class="text-muted mb-0 small">Department: {{ $cand->department }} · Candidate #{{ $cand->id }}</p>
                            </div>
                        </div>

                        <div class="p-3 bg-light rounded-3 mb-4 text-muted small" style="line-height: 1.5; white-space: pre-wrap; max-height:150px; overflow-y:auto;">
                            <strong class="text-dark d-block mb-1 small text-uppercase" style="letter-spacing:0.5px;">Platform / Manifesto:</strong>
                            {{ $cand->platform }}
                        </div>
                    </div>

                    <button type="button" class="btn btn-primary w-100 py-2.5 rounded-3 fw-bold vote-btn" 
                            onclick="confirmVote({{ $cand->id }}, '{{ e($cand->user->fullname) }}', '{{ $cand->photo_url ?? '' }}')">
                        <i class="bi bi-patch-check-fill me-1"></i> Vote for {{ $cand->user->fullname }}
                    </button>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5">
            <div class="mb-3" style="font-size:3rem;">👤</div>
            <h4 class="text-muted">No Candidates Registered</h4>
            <p class="text-muted small">No approved candidates registered for this position.</p>
            <form method="POST" action="{{ route('student.voting.skip') }}">
                @csrf
                <input type="hidden" name="position" value="{{ $currentPosition }}">
                <button type="submit" class="btn btn-secondary rounded-3 px-4 py-2">Proceed to Next Position</button>
            </form>
        </div>
    @endforelse
</div>

{{-- Quick Skip Option --}}
@if($candidates->count() > 0)
<div class="text-center mt-5">
    <form method="POST" action="{{ route('student.voting.skip') }}" onsubmit="return confirm('Skip this position without voting?')">
        @csrf
        <input type="hidden" name="position" value="{{ $currentPosition }}">
        <button type="submit" class="btn btn-link text-decoration-none text-muted small">
            I wish to skip voting for {{ $currentPosition }}
        </button>
    </form>
</div>
@endif

{{-- Expiration Alert Card (Overlay) --}}
<div id="expiration-overlay" class="custom-modal-overlay" style="display: none;">
    <div class="custom-modal-card text-center p-5">
        <div class="mb-3" style="font-size: 4rem;">⏰</div>
        <h3 class="fw-bold text-dark mb-2">Time is Up!</h3>
        <p class="text-muted mb-4 fs-6">Your 1-minute window for voting for <strong>{{ $currentPosition }}</strong> has expired. Your ballot was closed without a recorded vote.</p>
        <form method="POST" action="{{ route('student.voting.skip') }}">
            @csrf
            <input type="hidden" name="position" value="{{ $currentPosition }}">
            <button type="submit" class="btn btn-primary px-5 py-2.5 fw-bold" style="border-radius:10px;">
                Proceed to Next Position <i class="bi bi-arrow-right ms-1"></i>
            </button>
        </form>
    </div>
</div>

{{-- Vote Confirmation Modal --}}
<div id="confirm-modal" class="custom-modal-overlay" style="display: none;">
    <div class="custom-modal-card p-4">
        <h4 class="fw-bold text-dark mb-3">Confirm Your Selection</h4>
        <p class="text-muted">Are you absolutely sure you want to cast your official vote for:</p>
        
        <div class="p-3 bg-light rounded-4 d-flex align-items-center gap-3 mb-4">
            <div id="confirm-avatar" class="avatar bg-primary text-white fw-bold d-flex align-items-center justify-content-center" style="width:48px; height:48px; border-radius:50%; font-size:1.1rem;">
                A
            </div>
            <div>
                <h5 id="confirm-name" class="fw-bold text-dark mb-0">Candidate Name</h5>
                <p class="text-muted mb-0 small">Selected candidate for {{ $currentPosition }}</p>
            </div>
        </div>

        <div class="p-3 bg-warning bg-opacity-10 rounded-3 mb-4 text-warning-800 small border border-warning border-opacity-10">
            <i class="bi bi-info-circle-fill"></i> Once submitted, your vote cannot be changed or recalled. Your ballot session for this position will be finalized.
        </div>

        <div class="d-flex gap-2">
            <button type="button" class="btn btn-light w-50 py-2.5 rounded-3 fw-bold" onclick="closeConfirmModal()">Cancel</button>
            <form id="cast-vote-form" class="w-50" method="POST" action="{{ route('student.voting.cast') }}">
                @csrf
                <input type="hidden" name="candidacy_id" id="confirm-candidacy-id">
                <button type="submit" class="btn btn-success w-100 py-2.5 rounded-3 fw-bold">
                    <i class="bi bi-check-circle-fill me-1"></i> Submit Vote
                </button>
            </form>
        </div>
    </div>
</div>

<style>
    .hover-shadow {
        transition: all 0.25s ease-in-out;
    }
    .hover-shadow:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
        border-color: var(--primary) !important;
    }
    .custom-modal-overlay {
        position: fixed;
        inset: 0;
        z-index: 1050;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(8px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .custom-modal-card {
        background: #fff;
        width: 100%;
        max-width: 480px;
        border-radius: 24px;
        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.2);
        animation: slideUp 0.25s ease-out forwards;
    }
    @keyframes slideUp {
        from { transform: translateY(20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.08); }
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let secondsLeft = {{ $secondsRemaining }};
        const timerDisplay = document.getElementById('timer-display');
        const timerProgress = document.getElementById('timer-progress');
        const expirationOverlay = document.getElementById('expiration-overlay');
        const voteBtns = document.querySelectorAll('.vote-btn');
        
        const interval = setInterval(function() {
            secondsLeft--;
            
            // Format timer
            const pad = (n) => n < 10 ? '0' + n : n;
            timerDisplay.textContent = "0:" + pad(secondsLeft);
            
            // Progress bar width
            const percentage = (secondsLeft / 60) * 100;
            timerProgress.style.width = percentage + "%";
            
            if (secondsLeft <= 10) {
                timerDisplay.classList.add("text-danger");
                timerProgress.classList.add("bg-danger");
            }
            
            if (secondsLeft <= 0) {
                clearInterval(interval);
                // Disable voting buttons
                voteBtns.forEach(btn => btn.disabled = true);
                closeConfirmModal();
                // Show expiration card
                expirationOverlay.style.display = 'flex';
            }
        }, 1000);
    });

    // Fills an existing avatar box with the candidate's photo, or falls back to
    // their initial. The image borrows the box's radius so the shape is unchanged.
    function paintCandidateAvatar(box, photo, name) {
        if (!box) return;
        if (photo) {
            box.textContent = '';
            var img = document.createElement('img');
            img.src = photo;
            img.alt = name || '';
            img.style.cssText = 'width:100%; height:100%; border-radius:inherit; object-fit:cover; display:block;';
            box.appendChild(img);
        } else {
            box.textContent = (name || '?').charAt(0).toUpperCase();
        }
    }

    function confirmVote(id, name, photo) {
        document.getElementById('confirm-candidacy-id').value = id;
        document.getElementById('confirm-name').textContent = name;
        paintCandidateAvatar(document.getElementById('confirm-avatar'), photo, name);
        document.getElementById('confirm-modal').style.display = 'flex';
    }

    function closeConfirmModal() {
        document.getElementById('confirm-modal').style.display = 'none';
    }
</script>
@endsection
