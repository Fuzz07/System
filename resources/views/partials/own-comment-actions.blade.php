{{--
    Edit and Delete for a comment the viewer wrote; renders nothing on anyone
    else's. Pass $comment, $parent (the announcement or proposal it sits on)
    and $routes, a route-name prefix whose .update and .destroy take
    [parent, comment]. Include it right after the comment text, inside an
    element marked data-own-comment, and mark the text data-comment-text so
    Edit can swap it for the form.
--}}
@if((int) $comment->user_id === (int) auth()->id())
<div data-comment-actions style="display:flex;gap:14px;margin-top:6px;font-size:0.75rem;">
    <button type="button" data-comment-edit style="background:none;border:0;padding:0;color:var(--slate-500);font-weight:600;cursor:pointer;"><i class="bi bi-pencil"></i> Edit</button>
    <form method="POST" action="{{ route($routes . '.destroy', [$parent, $comment]) }}" data-confirm="Delete your comment permanently?" style="margin:0;">
        @csrf
        @method('DELETE')
        <button type="submit" style="background:none;border:0;padding:0;color:var(--danger);font-weight:600;cursor:pointer;"><i class="bi bi-trash"></i> Delete</button>
    </form>
</div>
<form method="POST" action="{{ route($routes . '.update', [$parent, $comment]) }}" data-comment-edit-form style="display:none;margin-top:6px;">
    @csrf
    @method('PUT')
    <textarea name="comment" rows="3" maxlength="2000" required aria-label="Edit your comment" style="width:100%;border:1px solid var(--slate-200);border-radius:12px;padding:10px 12px;font:inherit;font-size:0.85rem;resize:vertical;">{{ $comment->comment }}</textarea>
    <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:6px;">
        <button type="button" data-comment-edit-cancel style="background:none;border:1px solid var(--slate-200);border-radius:10px;padding:6px 14px;font-size:0.8rem;font-weight:600;color:var(--slate-500);cursor:pointer;">Cancel</button>
        <button type="submit" style="background:var(--primary);border:0;border-radius:10px;padding:6px 14px;font-size:0.8rem;font-weight:600;color:#fff;cursor:pointer;">Save</button>
    </div>
</form>

@pushOnce('scripts')
<script>
// Edit swaps a comment's text for its form. While the form is open it pauses
// the live refresh, which would otherwise redraw the comment and drop the edit.
// Listens while capturing: the mobile announcement sheet stops clicks bubbling.
document.addEventListener('click', function (event) {
    var toggle = event.target.closest('[data-comment-edit], [data-comment-edit-cancel]');
    if (!toggle) return;

    var comment = toggle.closest('[data-own-comment]');
    var form = comment.querySelector('[data-comment-edit-form]');
    var editing = toggle.hasAttribute('data-comment-edit');

    comment.querySelector('[data-comment-text]').style.display = editing ? 'none' : '';
    comment.querySelector('[data-comment-actions]').style.display = editing ? 'none' : 'flex';
    form.style.display = editing ? 'block' : 'none';
    form.toggleAttribute('data-ssc-live-pause', editing);

    if (editing) {
        form.querySelector('textarea').focus();
    } else {
        form.reset();
    }
}, true);
</script>
@endPushOnce
@endif
