{{--
    Announcement "post card": photo on top, caption underneath — the same look
    as the landing-page gallery. The title is stretched over the whole card, so
    clicking anywhere opens the dialog whose id is $modal.

    Pass $destroyUrl to show edit/delete controls on the photo; the edit button
    opens #editAnnModal{id}, which the page is expected to render.
    Pass $mine = true to tag the viewer's own posts.
--}}
@php $isLost = $a->category === \App\Models\Announcement::CATEGORY_LOST_ITEM; @endphp
<article class="post-card h-100 {{ $isLost ? 'category-lost' : '' }}">
    <div class="post-card-media">
        @if($a->image_path)
        <img src="{{ \App\Helpers\SscHelper::getUploadUrl($a->image_path) }}" alt="" loading="lazy" decoding="async">
        @else
        <div class="post-card-placeholder"><i class="bi {{ $isLost ? 'bi-search' : 'bi-megaphone' }}"></i></div>
        @endif

        <span class="announcement-chip post-card-badge {{ $isLost ? 'category-lost' : 'category-general' }}">
            <i class="bi {{ $isLost ? 'bi-search' : 'bi-megaphone' }}"></i> {{ $a->category_label }}
        </span>

        @if(!empty($destroyUrl))
        <div class="post-card-actions">
            <button type="button" class="btn btn-sm btn-light btn-icon" data-bs-toggle="modal" data-bs-target="#editAnnModal{{ $a->id }}" title="Edit announcement" aria-label="Edit announcement">
                <i class="bi bi-pencil"></i>
            </button>
            <form method="POST" action="{{ $destroyUrl }}" data-confirm="Delete this announcement permanently?">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-light btn-icon text-danger" title="Delete announcement" aria-label="Delete announcement">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        </div>
        @endif
    </div>

    <div class="post-card-caption">
        <button type="button" class="post-card-title stretched-link" data-bs-toggle="modal" data-bs-target="#{{ $modal }}">{{ $a->title }}</button>
        <p class="post-card-text">{{ Str::limit($a->content, 180) }}</p>
        <div class="post-card-meta">
            @if(!empty($mine))
            <span class="post-card-mine"><i class="bi bi-person-check-fill"></i> Your post</span>
            @else
            <span><i class="bi bi-person-circle"></i> {{ $a->author->fullname ?? 'SSC Admin' }}</span>
            @endif
            <span><i class="bi bi-clock"></i> {{ $a->created_at?->diffForHumans() }}</span>
            @if($a->relationLoaded('comments'))
            <span title="{{ $a->comments->count() }} {{ Str::plural('comment', $a->comments->count()) }}"><i class="bi bi-chat-dots"></i> {{ $a->comments->count() }}</span>
            @endif
        </div>
    </div>
</article>
