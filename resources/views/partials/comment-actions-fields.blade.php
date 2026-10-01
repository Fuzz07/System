{{-- The textarea and buttons shared by comment-actions' edit and reply forms. --}}
<textarea name="comment" rows="3" maxlength="2000" required aria-label="{{ $label }}" placeholder="{{ $placeholder }}" style="width:100%;border:1px solid var(--slate-200);border-radius:12px;padding:10px 12px;font:inherit;font-size:0.85rem;resize:vertical;">{{ $text }}</textarea>
<div style="display:flex;justify-content:flex-end;gap:8px;margin-top:6px;">
    <button type="button" data-comment-close style="background:none;border:1px solid var(--slate-200);border-radius:10px;padding:6px 14px;font-size:0.8rem;font-weight:600;color:var(--slate-500);cursor:pointer;">Cancel</button>
    <button type="submit" style="background:var(--primary);border:0;border-radius:10px;padding:6px 14px;font-size:0.8rem;font-weight:600;color:#fff;cursor:pointer;">{{ $submit }}</button>
</div>
