{{--
    Pop-up listing what was said on one proposal, opened by a button whose
    data-bs-target is "#feedbackModal{proposal id}". Pass $p, a proposal with
    comments.user and comments_count loaded. Students post anonymously, so
    their names stay hidden here too; replies sit under the comment they answer.
--}}
<div class="modal fade" id="feedbackModal{{ $p->id }}" tabindex="-1" aria-labelledby="feedbackModalTitle{{ $p->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:var(--radius);border:none;">
            <div class="modal-header modal-header-custom">
                <h5 class="modal-title" id="feedbackModalTitle{{ $p->id }}"><i class="bi bi-chat-dots"></i> Feedback &amp; Suggestions
                    ({{ $p->comments_count }})</h5><button type="button" class="btn-close"
                    data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="fw-bold mb-3" style="color:var(--navy-900);">{{ $p->project_title }}</div>
                <div class="d-flex flex-column gap-3">
                    @forelse(\App\Support\CommentThread::of($p->comments) as $c)
                        @php $isStudent = !$c->user || $c->user->role === 'student'; @endphp
                        {{-- Replies sit indented under the comment they answer. --}}
                        <div class="d-flex gap-3" @if($c->parent_id) style="margin-left:52px;" @endif>
                            <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0 {{ $isStudent ? 'bg-light text-muted' : 'bg-primary bg-opacity-10 text-primary' }}"
                                style="width:40px;height:40px;font-weight:700;">
                                @if($isStudent)<i class="bi bi-person-circle"></i>@else
                                {{ strtoupper(substr($c->user->fullname, 0, 1)) }}@endif
                            </div>
                            <div class="flex-fill">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <div>
                                        @if($isStudent)<span class="fw-bold text-dark small"><i
                                            class="bi bi-person-fill-lock"></i> Anonymous Student</span>
                                        @else <span class="fw-bold text-dark small">{{ $c->user->fullname }}</span> <span
                                            class="badge bg-primary bg-opacity-10 text-primary small ms-1"
                                        style="font-size:0.6rem;">{{ ucfirst($c->user->role) }}</span>@endif
                                    </div>
                                    <span class="text-muted small"
                                        style="font-size:0.7rem;">{{ $c->created_at?->diffForHumans() }}</span>
                                </div>
                                <div class="p-3 bg-light rounded-4 small text-dark" style="line-height:1.6;">
                                    {!! nl2br(e($c->comment)) !!}</div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted small"><i class="bi bi-chat"
                                style="font-size:1.6rem;opacity:.3;"></i>
                            <div class="mt-2">No feedback or suggestions on this proposal yet.</div>
                        </div>
                    @endforelse
                </div>
            </div>
            <div class="modal-footer border-0 pt-0"><button type="button" class="btn btn-outline-secondary"
                    data-bs-dismiss="modal">Close</button></div>
        </div>
    </div>
</div>
