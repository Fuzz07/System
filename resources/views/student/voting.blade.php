@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-student') @endsection

@section('content')
<div class="page-header">
    <div>
        <h1>Ballot & Voting Portal</h1>
        <p>Review approved candidates, read their manifestos, and cast your vote for each position</p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-9">
        @if(!$activeSy)
            <div class="alert alert-warning border-0 shadow-sm rounded-4">
                <i class="bi bi-exclamation-triangle-fill"></i> No active school year set. Voting is unavailable.
            </div>
        @else
            <div class="mb-4 p-3 bg-primary bg-opacity-10 border-0 rounded-4 d-flex align-items-center gap-3">
                <div style="font-size: 1.5rem;">🗳️</div>
                <div>
                    <h6 class="mb-1 text-primary fw-bold">Active Election: School Year {{ $activeSy->label }}</h6>
                    <p class="text-muted mb-0 small">You can cast exactly one vote per position. Please read each candidate's platform statement carefully before voting.</p>
                </div>
            </div>

            @php
                $positionsOrder = [
                    'SSC President',
                    'SSC Vice President',
                    'SSC Secretary',
                    'SSC Treasurer',
                    Auth::user()->department . ' Representative'
                ];
            @endphp

            @foreach($positionsOrder as $pos)
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px; overflow: hidden;">
                    <div class="card-header bg-light py-3 px-4 border-0 d-flex align-items-center justify-content-between">
                        <h5 class="mb-0 fw-bold text-dark">{{ $pos }}</h5>
                        @if(isset($myVotes[$pos]))
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill">
                                <i class="bi bi-check-circle-fill"></i> Vote Cast
                            </span>
                        @else
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-3 py-2 rounded-pill">
                                <i class="bi bi-hourglass-split"></i> Awaiting Vote
                            </span>
                        @endif
                    </div>

                    <div class="card-body p-4">
                        @if(isset($myVotes[$pos]))
                            @php
                                $votedCand = $myVotes[$pos]->candidacy;
                            @endphp
                            <div class="d-flex align-items-center gap-3 p-3 rounded-4 bg-light border border-success border-opacity-10">
                                <div class="avatar bg-success text-white fw-bold d-flex align-items-center justify-content-center" style="width:50px; height:50px; border-radius:50%; font-size:1.25rem;">
                                    {{ $votedCand->user->avatar }}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">{{ $votedCand->user->fullname }}</div>
                                    <div class="text-muted small">Department: {{ $votedCand->department }} · Candidate #{{ $votedCand->id }}</div>
                                </div>
                                <div class="ms-auto text-success fw-bold small">
                                    <i class="bi bi-patch-check-fill"></i> Voted
                                </div>
                            </div>
                        @else
                            @if(empty($candidatesByPosition[$pos]))
                                <div class="text-center py-4 text-muted">
                                    <i class="bi bi-person-x" style="font-size: 2rem;"></i>
                                    <p class="mb-0 mt-2 small">No approved candidates for this position in SY {{ $activeSy->label }}</p>
                                </div>
                            @else
                                <div class="row g-3">
                                    @foreach($candidatesByPosition[$pos] as $cand)
                                        <div class="col-md-6">
                                            <div class="p-3 border rounded-4 h-100 d-flex flex-column justify-content-between transition-all hover-shadow">
                                                <div class="d-flex align-items-center gap-3 mb-3">
                                                    <div class="avatar bg-primary text-white fw-bold d-flex align-items-center justify-content-center" style="width:46px; height:46px; border-radius:50%; font-size:1.1rem;">
                                                        {{ $cand->user->avatar }}
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-dark">{{ $cand->user->fullname }}</div>
                                                        <div class="text-muted small" style="font-size:0.75rem;">Department: {{ $cand->department }}</div>
                                                    </div>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <span class="text-muted small d-block mb-1">Vision / Platform Summary:</span>
                                                    <p class="text-muted small mb-0 text-truncate" style="max-width: 100%;">{{ $cand->platform }}</p>
                                                </div>

                                                <button type="button" class="btn btn-outline-primary btn-sm w-100 py-2 rounded-3"
                                                    data-candidacy-id="{{ $cand->id }}"
                                                    data-candidate-name="{{ e($cand->user->fullname) }}"
                                                    data-position="{{ e($pos) }}"
                                                    data-platform="{{ e($cand->platform) }}"
                                                    onclick="openVotingModal(this)">
                                                    <i class="bi bi-box-arrow-in-up"></i> View Platform & Vote
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>

{{-- Custom Voting & Timer Modal Overlay --}}
<div id="votingModalOverlay" class="custom-modal-overlay" style="display: none;">
    <div class="custom-modal-card">
        <div class="custom-modal-header">
            <h5 class="fw-bold text-dark mb-0">Candidate Platform Review</h5>
            <button type="button" class="btn-close-custom" onclick="closeVotingModal()"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="custom-modal-body">
            <div class="d-flex align-items-center gap-3 mb-4 p-3 rounded-4 bg-light">
                <div class="avatar bg-primary text-white fw-bold d-flex align-items-center justify-content-center" id="modalCandAvatar" style="width:60px; height:60px; border-radius:50%; font-size:1.4rem;">
                    A
                </div>
                <div>
                    <h5 class="fw-bold text-dark mb-0" id="modalCandName">Candidate Name</h5>
                    <p class="text-muted mb-0 small" id="modalCandPos">Position</p>
                </div>
            </div>

            <div class="mb-4">
                <label class="fw-bold text-dark mb-2 small uppercase-label">Manifesto & Platform</label>
                <div id="modalCandPlatform" class="p-3 bg-light rounded-4 text-muted border-0 overflow-auto" style="max-height: 200px; font-size: 0.88rem; line-height: 1.6; white-space: pre-wrap;">
                    Platform details here...
                </div>
            </div>

            <div id="timerBox" class="timer-box p-3 mb-4 border border-warning rounded-4 bg-warning bg-opacity-10 d-flex flex-column gap-2">
                <div class="d-flex align-items-center justify-content-between">
                    <span class="small fw-bold text-warning"><i class="bi bi-clock-fill"></i> <span id="timerLabel">Voting Window</span></span>
                    <span id="countdownText" class="fw-bold" style="font-size: 1.1rem; color: #b45309;">1:00</span>
                </div>
                <div class="progress" style="height: 6px; background-color: rgba(217, 119, 6, 0.1); border-radius: 99px;">
                    <div id="timerProgressBar" class="progress-bar bg-warning" role="progressbar" style="width: 100%; border-radius: 99px; transition: width 1s linear;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <p id="timerHint" class="text-muted mb-0" style="font-size:0.75rem; line-height: 1.4;">
                    You have <strong>1 minute</strong> to review the platform and cast your vote. If time runs out, you will <strong>not</strong> be able to vote for this candidate.
                </p>
            </div>

            <form id="voteForm" method="POST" action="{{ route('student.voting.store') }}">
                @csrf
                <input type="hidden" name="candidacy_id" id="modalCandidacyId">
                <button type="submit" id="submitVoteBtn" class="btn btn-success w-100 py-3 rounded-4 fw-bold justify-content-center" disabled>
                    <i class="bi bi-check-circle-fill"></i> Confirm Vote (60s)
                </button>
            </form>
        </div>
    </div>
</div>

<style>
    /* Premium Visual Hover Effects */
    .hover-shadow {
        transition: all 0.25s ease-in-out;
    }
    .hover-shadow:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        border-color: var(--primary-light) !important;
    }

    /* Custom Premium Modal styles */
    .custom-modal-overlay {
        position: fixed;
        inset: 0;
        z-index: 1050;
        background: rgba(15, 23, 42, 0.5);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        animation: fadeInOverlay 0.25s ease forwards;
    }

    .custom-modal-card {
        background: #fff;
        width: 100%;
        max-width: 520px;
        border-radius: 28px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        border: 1px solid rgba(255, 255, 255, 0.2);
        overflow: hidden;
        animation: slideUpCard 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
    }

    .custom-modal-header {
        padding: 20px 24px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .btn-close-custom {
        background: none;
        border: none;
        color: #94a3b8;
        font-size: 1.1rem;
        cursor: pointer;
        padding: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: color 0.15s;
    }
    .btn-close-custom:hover {
        color: #0f172a;
    }

    .custom-modal-body {
        padding: 24px;
    }

    .uppercase-label {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
    }

    @keyframes fadeInOverlay {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideUpCard {
        from { transform: translateY(30px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
</style>

<script>
    let countdownInterval = null;
    const expiredCandidacyIds = new Set(); // track IDs whose window expired

    const votingModalOverlay = document.getElementById('votingModalOverlay');
    const modalCandAvatar    = document.getElementById('modalCandAvatar');
    const modalCandName      = document.getElementById('modalCandName');
    const modalCandPos       = document.getElementById('modalCandPos');
    const modalCandPlatform  = document.getElementById('modalCandPlatform');
    const modalCandidacyId   = document.getElementById('modalCandidacyId');
    const submitVoteBtn      = document.getElementById('submitVoteBtn');
    const countdownText      = document.getElementById('countdownText');
    const timerProgressBar   = document.getElementById('timerProgressBar');
    const timerBox           = document.getElementById('timerBox');
    const timerLabel         = document.getElementById('timerLabel');
    const timerHint          = document.getElementById('timerHint');

    function openVotingModal(button) {
        if (countdownInterval) {
            clearInterval(countdownInterval);
            countdownInterval = null;
        }

        const candidacyId = button.dataset.candidacyId;
        const candName = button.dataset.candidateName;
        const position = button.dataset.position;
        const platform = button.dataset.platform;

        // Populate modal content
        modalCandName.textContent     = candName;
        modalCandPos.textContent      = position;
        modalCandPlatform.textContent = platform;
        modalCandidacyId.value        = candidacyId;
        modalCandAvatar.textContent   = candName.charAt(0).toUpperCase();

        votingModalOverlay.style.display = 'flex';

        // ── If this candidacy's window already expired, show locked state ──
        if (expiredCandidacyIds.has(candidacyId)) {
            showExpiredState();
            return;
        }

        // ── Fresh window: enable vote button immediately, start countdown ──
        const totalDuration = 60;
        let timeLeft = totalDuration;

        // Reset timer UI
        timerBox.className = timerBox.className.replace('border-danger', 'border-warning').replace('bg-danger', 'bg-warning');
        timerLabel.textContent = 'Voting Window';
        timerHint.innerHTML = 'You have <strong>1 minute</strong> to review the platform and cast your vote. If time runs out, you will <strong>not</strong> be able to vote for this candidate.';
        countdownText.style.color = '#b45309';
        countdownText.textContent = formatTime(timeLeft);
        timerProgressBar.style.width = '100%';
        timerProgressBar.className = 'progress-bar bg-warning';
        timerProgressBar.setAttribute('aria-valuenow', 100);

        // Enable the vote button right away
        submitVoteBtn.disabled = false;
        submitVoteBtn.className = 'btn btn-success w-100 py-3 rounded-4 fw-bold justify-content-center';
        submitVoteBtn.innerHTML = `<i class="bi bi-check-circle-fill"></i> Confirm Vote (${timeLeft}s)`;

        countdownInterval = setInterval(() => {
            timeLeft--;

            const percentage = (timeLeft / totalDuration) * 100;
            timerProgressBar.style.width = `${percentage}%`;
            timerProgressBar.setAttribute('aria-valuenow', percentage);
            countdownText.textContent = formatTime(timeLeft);
            submitVoteBtn.innerHTML = `<i class="bi bi-check-circle-fill"></i> Confirm Vote (${timeLeft}s)`;

            // Turn red warning at ≤10s
            if (timeLeft <= 10) {
                timerProgressBar.className = 'progress-bar bg-danger';
                countdownText.style.color = '#dc2626';
                submitVoteBtn.className = 'btn btn-warning w-100 py-3 rounded-4 fw-bold justify-content-center';
            }

            if (timeLeft <= 0) {
                clearInterval(countdownInterval);
                countdownInterval = null;
                expiredCandidacyIds.add(candidacyId); // permanently record expiry
                showExpiredState();
            }
        }, 1000);
    }

    function showExpiredState() {
        // Disable submit
        submitVoteBtn.disabled = true;
        submitVoteBtn.className = 'btn btn-secondary w-100 py-3 rounded-4 fw-bold justify-content-center opacity-75';
        submitVoteBtn.innerHTML = '<i class="bi bi-clock-history"></i> Time Expired — Cannot Vote';

        // Update timer box to danger
        countdownText.textContent = '0:00';
        countdownText.style.color = '#dc2626';
        timerProgressBar.style.width = '0%';
        timerProgressBar.className = 'progress-bar bg-danger';
        timerLabel.textContent = 'Voting Window Closed';
        timerHint.innerHTML = '⏰ Your 1-minute voting window for this candidate has expired. You can no longer vote for them.';

        // Prevent closing modal via backdrop click (show it as informational)
    }

    function closeVotingModal() {
        if (countdownInterval) {
            clearInterval(countdownInterval);
            countdownInterval = null;
        }
        votingModalOverlay.style.display = 'none';
    }

    function formatTime(seconds) {
        const m = Math.floor(seconds / 60);
        const s = seconds % 60;
        return `${m}:${s.toString().padStart(2, '0')}`;
    }

    // Allow closing via backdrop click (users can still dismiss the expired modal)
    votingModalOverlay.addEventListener('click', function(e) {
        if (e.target === this) closeVotingModal();
    });
</script>
@endsection
