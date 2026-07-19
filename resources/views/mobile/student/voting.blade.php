@extends('layouts.mobile-student', ['pageTitle' => 'Cast Vote', 'showBack' => true, 'backUrl' => route('mobile.student.proposals')])

@section('content')
@php
    $student = Auth::user();
@endphp
<div style="padding: 12px 16px;">
    @if(!$activeSy)
        <div class="m-alert m-alert-danger">
            <i class="bi bi-exclamation-triangle-fill"></i> No active school year set.
        </div>
    @else
        <div style="background: rgba(79, 70, 229, 0.08); border-radius: 14px; padding: 12px 14px; margin-bottom: 18px; font-size: 0.78rem; color: var(--primary-dark); line-height: 1.4;">
            <strong>Election SY {{ $activeSy->label }}</strong><br>
            Please read the platforms of the candidates. You may cast one vote per position.
        </div>

        @php
            $positionsOrder = [
                'SSC President',
                'SSC Vice President',
                'SSC Secretary',
                'SSC Treasurer',
                $student->department . ' Representative'
            ];
        @endphp

        @foreach($positionsOrder as $pos)
            <div class="m-card elevated" style="padding: 16px; margin-bottom: 18px;">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--slate-100); padding-bottom: 10px; margin-bottom: 12px;">
                    <h3 style="font-size: 0.95rem; font-weight: 800; color: var(--slate-900); margin: 0;">{{ $pos }}</h3>
                    @if(isset($myVotes[$pos]))
                        <span class="m-badge approved" style="font-size: 0.65rem;"><i class="bi bi-check-circle-fill"></i> Voted</span>
                    @else
                        <span class="m-badge pending" style="font-size: 0.65rem;"><i class="bi bi-hourglass-split"></i> Awaiting</span>
                    @endif
                </div>

                @if(isset($myVotes[$pos]))
                    @php
                        $votedCand = $myVotes[$pos]->candidacy;
                    @endphp
                    <div style="display: flex; align-items: center; gap: 10px; background: var(--slate-50); border: 1px solid var(--slate-100); padding: 10px 12px; border-radius: 12px;">
                        <div class="avatar bg-success text-white fw-bold d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; border-radius: 50%; font-size: 0.95rem;">
                            {{ $votedCand->user->avatar }}
                        </div>
                        <div>
                            <div style="font-size: 0.82rem; font-weight: 700; color: var(--slate-800);">{{ $votedCand->user->fullname }}</div>
                            <div style="font-size: 0.68rem; color: var(--slate-400);">Voted candidate #{{ $votedCand->id }}</div>
                        </div>
                    </div>
                @else
                    @if(empty($candidatesByPosition[$pos]))
                        <div style="text-align: center; padding: 14px 0; color: var(--slate-400); font-size: 0.76rem;">
                            <i class="bi bi-person-x" style="font-size: 1.5rem; display: block; margin-bottom: 4px;"></i>
                            No approved candidates
                        </div>
                    @else
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            @foreach($candidatesByPosition[$pos] as $cand)
                                <div style="border: 1px solid var(--slate-200); border-radius: 12px; padding: 12px; display: flex; flex-direction: column; justify-content: space-between;">
                                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                                        <div class="avatar bg-primary text-white fw-bold d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; border-radius: 50%; font-size: 0.9rem;">
                                            {{ $cand->user->avatar }}
                                        </div>
                                        <div>
                                            <div style="font-size: 0.8rem; font-weight: 700; color: var(--slate-800);">{{ $cand->user->fullname }}</div>
                                            <div style="font-size: 0.66rem; color: var(--slate-400);">{{ $cand->department }}</div>
                                        </div>
                                    </div>
                                    <p style="font-size: 0.74rem; color: var(--slate-500); line-height: 1.4; margin-bottom: 10px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        {{ $cand->platform }}
                                    </p>
                                    <button type="button" class="install-banner-btn" style="width: 100%; border: 1px solid var(--primary); background: transparent; color: var(--primary); padding: 8px; font-size: 0.76rem; border-radius: 8px; font-weight: 700; cursor: pointer;"
                                        data-candidacy-id="{{ $cand->id }}"
                                        data-candidate-name="{{ e($cand->user->fullname) }}"
                                        data-position="{{ e($pos) }}"
                                        data-platform="{{ e($cand->platform) }}"
                                        onclick="openMobileVotingModal(this)">
                                        View & Vote
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>
        @endforeach
    @endif
</div>

{{-- Mobile Custom Voting Overlay --}}
<div id="mobileVotingModal" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(4px); z-index: 9999; align-items: flex-end; justify-content: center;">
    <div style="background: #fff; width: 100%; max-width: 480px; border-radius: 24px 24px 0 0; padding: 20px 16px; box-sizing: border-box; display: flex; flex-direction: column; max-height: 90vh;">
        <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 12px; border-bottom: 1px solid var(--slate-150);">
            <h3 style="font-size: 1rem; font-weight: 800; color: var(--slate-900); margin: 0;">Review Platform</h3>
            <button onclick="closeMobileVotingModal()" style="background: none; border: none; font-size: 1.1rem; color: var(--slate-400); cursor: pointer;"><i class="bi bi-x-lg"></i></button>
        </div>

        <div style="overflow-y: auto; padding: 16px 0; flex: 1;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 16px; background: var(--slate-50); padding: 10px; border-radius: 12px;">
                <div class="avatar bg-primary text-white fw-bold d-flex align-items-center justify-content-center" id="mobileModalAvatar" style="width: 44px; height: 44px; border-radius: 50%; font-size: 1.1rem;">
                    A
                </div>
                <div>
                    <div style="font-size: 0.9rem; font-weight: 700; color: var(--slate-800);" id="mobileModalCandName">Candidate Name</div>
                    <div style="font-size: 0.72rem; color: var(--slate-400);" id="mobileModalCandPos">Position</div>
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: var(--slate-500); margin-bottom: 6px;">Vision / Platform</label>
                <div id="mobileModalCandPlatform" style="font-size: 0.78rem; color: var(--slate-600); line-height: 1.5; background: var(--slate-50); padding: 12px; border-radius: 10px; white-space: pre-wrap; max-height: 160px; overflow-y: auto;">Platform text</div>
            </div>

            <div id="mobileTimerBox" style="background: rgba(217, 119, 6, 0.08); border: 1px solid rgba(217, 119, 6, 0.2); border-radius: 12px; padding: 12px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; font-size: 0.74rem; font-weight: 700; color: #b45309;">
                    <span><i class="bi bi-clock-fill"></i> <span id="mobileTimerLabel">Voting Window</span></span>
                    <span id="mobileCountdownText">1:00</span>
                </div>
                <div style="height: 4px; background: rgba(217, 119, 6, 0.1); border-radius: 10px; overflow: hidden;">
                    <div id="mobileTimerProgressBar" style="height: 100%; background: #f59e0b; width: 100%; transition: width 1s linear;"></div>
                </div>
                <p id="mobileTimerHint" style="font-size: 0.68rem; color: var(--slate-500); margin-top: 6px; line-height: 1.3; margin-bottom: 0;">
                    You have <strong>1 minute</strong> to review and vote. If time runs out, you cannot vote for this candidate.
                </p>
            </div>
        </div>

        <form id="mobileVoteForm" method="POST" action="{{ route('mobile.student.voting.store') }}">
            @csrf
            <input type="hidden" name="candidacy_id" id="mobileModalCandidacyId">
            <button type="submit" id="mobileSubmitVoteBtn" style="width: 100%; padding: 12px; background: #22c55e; color: #fff; border: none; border-radius: 10px; font-size: 0.88rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                <i class="bi bi-check-circle-fill"></i> Confirm Vote (60s)
            </button>
        </form>
    </div>
</div>

<script>
    let mobileCountdownInterval = null;
    const mobileExpiredIds = new Set(); // track candidacy IDs whose window expired

    const mobileVotingModal      = document.getElementById('mobileVotingModal');
    const mobileModalAvatar      = document.getElementById('mobileModalAvatar');
    const mobileModalCandName    = document.getElementById('mobileModalCandName');
    const mobileModalCandPos     = document.getElementById('mobileModalCandPos');
    const mobileModalCandPlatform= document.getElementById('mobileModalCandPlatform');
    const mobileModalCandidacyId = document.getElementById('mobileModalCandidacyId');
    const mobileSubmitVoteBtn    = document.getElementById('mobileSubmitVoteBtn');
    const mobileCountdownText    = document.getElementById('mobileCountdownText');
    const mobileTimerProgressBar = document.getElementById('mobileTimerProgressBar');
    const mobileTimerLabel       = document.getElementById('mobileTimerLabel');
    const mobileTimerHint        = document.getElementById('mobileTimerHint');

    function openMobileVotingModal(button) {
        if (mobileCountdownInterval) {
            clearInterval(mobileCountdownInterval);
            mobileCountdownInterval = null;
        }

        const candidacyId = button.dataset.candidacyId;
        const candName = button.dataset.candidateName;
        const position = button.dataset.position;
        const platform = button.dataset.platform;

        mobileModalCandName.textContent     = candName;
        mobileModalCandPos.textContent      = position;
        mobileModalCandPlatform.textContent = platform;
        mobileModalCandidacyId.value        = candidacyId;
        mobileModalAvatar.textContent       = candName.charAt(0).toUpperCase();

        mobileVotingModal.style.display = 'flex';

        // ── Already expired? Show locked state immediately ──
        if (mobileExpiredIds.has(candidacyId)) {
            showMobileExpiredState();
            return;
        }

        // ── Fresh window: enable button immediately, start countdown ──
        const totalDuration = 60;
        let timeLeft = totalDuration;

        // Reset UI
        mobileTimerLabel.textContent = 'Voting Window';
        mobileTimerHint.innerHTML = 'You have <strong>1 minute</strong> to review and vote. If time runs out, you cannot vote for this candidate.';
        mobileCountdownText.textContent = formatTime(timeLeft);
        mobileCountdownText.style.color = '#b45309';
        mobileTimerProgressBar.style.background = '#f59e0b';
        mobileTimerProgressBar.style.width = '100%';

        // Enable button immediately
        mobileSubmitVoteBtn.disabled = false;
        mobileSubmitVoteBtn.style.background = '#22c55e';
        mobileSubmitVoteBtn.style.cursor = 'pointer';
        mobileSubmitVoteBtn.innerHTML = `<i class="bi bi-check-circle-fill"></i> Confirm Vote (${timeLeft}s)`;

        mobileCountdownInterval = setInterval(() => {
            timeLeft--;

            mobileCountdownText.textContent = formatTime(timeLeft);
            mobileSubmitVoteBtn.innerHTML = `<i class="bi bi-check-circle-fill"></i> Confirm Vote (${timeLeft}s)`;

            const percentage = (timeLeft / totalDuration) * 100;
            mobileTimerProgressBar.style.width = `${percentage}%`;

            // Turn red at ≤10s remaining
            if (timeLeft <= 10) {
                mobileTimerProgressBar.style.background = '#ef4444';
                mobileCountdownText.style.color = '#dc2626';
                mobileSubmitVoteBtn.style.background = '#f59e0b';
            }

            if (timeLeft <= 0) {
                clearInterval(mobileCountdownInterval);
                mobileCountdownInterval = null;
                mobileExpiredIds.add(candidacyId);
                showMobileExpiredState();
            }
        }, 1000);
    }

    function showMobileExpiredState() {
        mobileSubmitVoteBtn.disabled = true;
        mobileSubmitVoteBtn.style.background = '#94a3b8';
        mobileSubmitVoteBtn.style.cursor = 'not-allowed';
        mobileSubmitVoteBtn.innerHTML = '<i class="bi bi-clock-history"></i> Time Expired — Cannot Vote';

        mobileCountdownText.textContent = '0:00';
        mobileCountdownText.style.color = '#dc2626';
        mobileTimerProgressBar.style.width = '0%';
        mobileTimerProgressBar.style.background = '#ef4444';
        mobileTimerLabel.textContent = 'Voting Window Closed';
        mobileTimerHint.innerHTML = '⏰ Your 1-minute voting window for this candidate has expired.';
    }

    function closeMobileVotingModal() {
        if (mobileCountdownInterval) {
            clearInterval(mobileCountdownInterval);
            mobileCountdownInterval = null;
        }
        mobileVotingModal.style.display = 'none';
    }

    function formatTime(seconds) {
        const m = Math.floor(seconds / 60);
        const s = seconds % 60;
        return `${m}:${s.toString().padStart(2, '0')}`;
    }

    mobileVotingModal.addEventListener('click', function(e) {
        if (e.target === this) closeMobileVotingModal();
    });
</script>
@endsection
