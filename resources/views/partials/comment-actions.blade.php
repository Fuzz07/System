{{--
    The actions under one comment in a student's thread: Reply on top-level
    comments, and Edit and Delete on the ones the viewer wrote. Pass $comment,
    $parent (the announcement or proposal it sits on) and $routes, a route-name
    prefix whose .reply, .update and .destroy take [parent, comment]. Include
    it right after the comment text, inside the element marked data-comment
    that wraps that one comment, and mark the text data-comment-text so Edit
    can swap it for the form.
--}}
@php
    $canReply = $comment->parent_id === null;
    $isOwn = (int) $comment->user_id === (int) auth()->id();
    $linkStyle = 'background:none;border:0;padding:0;font-weight:600;cursor:pointer;';
@endphp
@if($canReply || $isOwn)
<div data-comment-actions style="display:flex;gap:14px;margin-top:6px;font-size:0.75rem;">
    @if($canReply)
    <button type="button" data-comment-open="reply" style="{{ $linkStyle }}color:var(--slate-500);"><i class="bi bi-reply"></i> Reply</button>
    @endif
    @if($isOwn)
    <button type="button" data-comment-open="edit" style="{{ $linkStyle }}color:var(--slate-500);"><i class="bi bi-pencil"></i> Edit</button>
    <form method="POST" action="{{ route($routes . '.destroy', [$parent, $comment]) }}" data-confirm="{{ $canReply ? 'Delete your comment and any replies to it?' : 'Delete your reply permanently?' }}" style="margin:0;">
        @csrf
        @method('DELETE')
        <button type="submit" style="{{ $linkStyle }}color:var(--danger);"><i class="bi bi-trash"></i> Delete</button>
    </form>
    @endif
</div>
@endif

@if($isOwn)
<form method="POST" action="{{ route($routes . '.update', [$parent, $comment]) }}" data-comment-form="edit" style="display:none;margin-top:6px;">
    @csrf
    @method('PUT')
    @include('partials.comment-actions-fields', ['text' => $comment->comment, 'label' => 'Edit your comment', 'placeholder' => '', 'submit' => 'Save'])
</form>
@endif

@if($canReply)
<form method="POST" action="{{ route($routes . '.reply', [$parent, $comment]) }}" data-comment-form="reply" style="display:none;margin-top:6px;">
    @csrf
    @include('partials.comment-actions-fields', ['text' => '', 'label' => 'Write a reply', 'placeholder' => 'Write a reply…', 'submit' => 'Reply'])
</form>
@endif

@pushOnce('scripts')
<script>
// Reply opens a form under the comment; Edit swaps the comment's text for one.
// While a form is open it pauses the live refresh, which would otherwise
// redraw the thread and drop what is being typed. Listens while capturing:
// the mobile announcement sheet stops clicks bubbling.
document.addEventListener('click', function (event) {
    var button = event.target.closest('[data-comment-open], [data-comment-close]');
    if (!button) return;

    var comment = button.closest('[data-comment]');
    var opening = button.hasAttribute('data-comment-open');
    var form = opening
        ? comment.querySelector(':scope > [data-comment-form="' + button.dataset.commentOpen + '"]')
        : button.closest('[data-comment-form]');

    if (form.dataset.commentForm === 'edit') {
        comment.querySelector(':scope > [data-comment-text]').style.display = opening ? 'none' : '';
    }
    comment.querySelector(':scope > [data-comment-actions]').style.display = opening ? 'none' : 'flex';
    form.style.display = opening ? 'block' : 'none';
    form.toggleAttribute('data-ssc-live-pause', opening);

    if (opening) {
        form.querySelector('textarea').focus();
    } else {
        form.reset();
    }
}, true);
</script>
@endPushOnce
