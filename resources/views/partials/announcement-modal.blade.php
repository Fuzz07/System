{{--
    Full view of one announcement, opened from its post card.
    Pass $commentRoute to show the comment thread with a form to post to it.
    Pass $showComments = true to show the thread read-only (staff pages).
    Pass $commentDestroyRoute (a route name taking [announcement, comment]) to
    add a remove button to each comment.
    Pass $editModal (an element id) to show an Edit button that opens it.
--}}
@php $isLost = $a->category === \App\Models\Announcement::CATEGORY_LOST_ITEM; @endphp
<div class="modal fade" id="{{ $modal }}" tabindex="-1" aria-labelledby="{{ $modal }}Title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg"><div class="modal-content" style="border-radius:24px;border:none;overflow:hidden;">
        <div class="modal-header border-0 p-4 pb-0"><button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Close"></button></div>
        <div class="modal-body p-4 p-md-5 pt-0">
            <div class="text-center mb-4">
                <span class="announcement-chip {{ $isLost ? 'category-lost' : 'category-general' }}">
                    <i class="bi {{ $isLost ? 'bi-search' : 'bi-megaphone' }}"></i> {{ $a->category_label }}
                </span>
                <h2 class="fw-bold text-dark h3 px-md-5 mt-2" id="{{ $modal }}Title">{{ $a->title }}</h2>
                <div class="text-muted small mt-2"><i class="bi bi-person"></i> {{ $a->author->fullname ?? 'SSC Admin' }} &bull; <i class="bi bi-calendar3"></i> {{ $a->created_at?->format('F d, Y') }}</div>
            </div>
            @if($a->image_path)
            <img src="{{ \App\Helpers\SscHelper::getUploadUrl($a->image_path) }}" alt="" loading="lazy" style="width:100%; max-height:420px; border-radius:16px; margin-bottom:24px; object-fit:cover; display:block;">
            @else
            <hr class="opacity-10 my-4">
            @endif
            <div style="font-size:1.05rem;line-height:1.9;color:var(--slate-700);">{!! nl2br(e($a->content)) !!}</div>
            @if($a->project_id && $a->proposal?->completion_proof)
            <div class="mt-5 p-4 rounded-4 bg-success bg-opacity-10 border border-success border-opacity-20 d-flex justify-content-between align-items-center gap-3">
                <div><h6 class="fw-bold text-success mb-1"><i class="bi bi-shield-check"></i> Verified Audit Proof</h6><div class="text-muted small">Official receipt available.</div></div>
                <a href="{{ \App\Helpers\SscHelper::getUploadUrl($a->proposal->completion_proof) }}" target="_blank" rel="noopener" class="btn btn-success px-4" style="border-radius:10px;font-weight:600;"><i class="bi bi-receipt"></i> View Receipt</a>
            </div>
            @endif

            @if(!empty($commentRoute) || !empty($showComments))
            <hr class="opacity-10 my-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-chat-dots"></i> Comments ({{ $a->comments->count() }})</h6>

            @if(!empty($commentRoute))
            <form method="POST" action="{{ $commentRoute }}" class="mb-4">
                @csrf
                <div class="mb-2"><textarea name="comment" class="form-control-custom" rows="2" placeholder="{{ $isLost ? 'Found this item, or know whose it is? Leave a comment...' : 'Share your thoughts or feedback on this announcement...' }}" required style="border-radius:12px;"></textarea></div>
                <button type="submit" class="btn-primary-custom px-4" style="padding:8px 18px;font-size:0.85rem;">Post Comment <i class="bi bi-send ms-1"></i></button>
            </form>
            @endif

            {{-- Students post anonymously, so their names stay hidden on staff pages too. --}}
            <div class="d-flex flex-column gap-3">
                @forelse($a->comments as $c)
                <div class="d-flex gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0 bg-light text-muted" style="width:38px;height:38px;font-size:1.1rem;">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <div class="flex-fill">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-dark small"><i class="bi bi-person-fill-lock"></i> Anonymous Student</span>
                            <span class="d-inline-flex align-items-center gap-2">
                                <span class="text-muted small" style="font-size:0.7rem;">{{ $c->created_at?->diffForHumans() }}</span>
                                @if(!empty($commentDestroyRoute))
                                <form method="POST" action="{{ route($commentDestroyRoute, [$a, $c]) }}" data-confirm="Remove this comment permanently?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-link text-danger p-0 lh-1" title="Remove comment" aria-label="Remove comment"><i class="bi bi-trash"></i></button>
                                </form>
                                @endif
                            </span>
                        </div>
                        <div class="p-3 bg-light rounded-4 small text-dark" style="line-height:1.6;">{!! nl2br(e($c->comment)) !!}</div>
                    </div>
                </div>
                @empty
                <div class="text-center py-3 text-muted small">
                    @if(!empty($commentRoute))
                    {{ $isLost ? 'No comments yet. Be the first to help!' : 'No comments yet. Be the first to share your thoughts!' }}
                    @else
                    No student comments on this announcement yet.
                    @endif
                </div>
                @endforelse
            </div>
            @endif
        </div>
        <div class="modal-footer border-0 p-4 bg-light bg-opacity-50 flex-nowrap gap-2">
            @if(!empty($editModal))
            {{-- Bootstrap closes this dialog before opening the edit form. --}}
            <button type="button" class="btn btn-brand d-inline-flex align-items-center justify-content-center gap-2 w-100" data-bs-toggle="modal" data-bs-target="#{{ $editModal }}" style="border-radius:12px;padding:12px;font-weight:600;"><i class="bi bi-pencil"></i> Edit</button>
            @endif
            <button type="button" class="btn btn-secondary w-100 m-0" data-bs-dismiss="modal" style="border-radius:12px;padding:12px;font-weight:600;">Close</button>
        </div>
    </div></div>
</div>
