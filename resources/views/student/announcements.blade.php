@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-student') @endsection

@section('content')
<div class="page-header"><div><h1>Announcements</h1><p>Official announcements from the Supreme Student Council</p></div></div>

<div class="category-filter-bar">
    <a href="{{ route('student.announcements') }}" class="{{ !$category ? 'active' : '' }}"><i class="bi bi-grid"></i> All</a>
    <a href="{{ route('student.announcements', ['category' => 'general']) }}" class="{{ $category === 'general' ? 'active' : '' }}"><i class="bi bi-megaphone"></i> General</a>
    <a href="{{ route('student.announcements', ['category' => 'lost_item']) }}" class="{{ $category === 'lost_item' ? 'active' : '' }}"><i class="bi bi-search"></i> Lost &amp; Found</a>
</div>

<div class="row g-4">
@forelse($announcements as $a)
<div class="col-12 col-md-6">
<div class="announcement-card h-100 d-flex flex-column {{ $a->category === 'lost_item' ? 'category-lost' : '' }}">
    <div class="d-flex justify-content-between align-items-start gap-3">
        <div style="flex:1; min-width:0;">
            <span class="announcement-chip {{ $a->category === 'lost_item' ? 'category-lost' : 'category-general' }}">
                <i class="bi {{ $a->category === 'lost_item' ? 'bi-search' : 'bi-megaphone' }}"></i> {{ $a->category_label }}
            </span>
            <h2 class="announcement-title" style="font-size:1.1rem;">{{ $a->title }}</h2>
        </div>
        <span class="announcement-chip role" style="flex-shrink:0;"><i class="bi bi-calendar3"></i> {{ $a->created_at?->format('M d, Y') }}</span>
    </div>
    @if($a->image_path)
    <img src="{{ \App\Helpers\SscHelper::getUploadUrl($a->image_path) }}" alt="" class="announcement-image" style="max-height:260px;">
    @endif
    <div class="announcement-body">{!! nl2br(e(Str::limit($a->content, 250))) !!}</div>
    <div class="d-flex justify-content-between align-items-center mt-auto">
        <div style="font-size:.78rem;color:var(--slate-400);display:flex;align-items:center;gap:14px;">
            <span><i class="bi bi-person-circle"></i> {{ $a->author->fullname ?? 'SSC Admin' }}</span>
            <span><i class="bi bi-clock"></i> {{ $a->created_at?->diffForHumans() }}</span>
            @if($a->category === 'lost_item')
            <span><i class="bi bi-chat-dots"></i> {{ $a->comments->count() }}</span>
            @endif
        </div>
        <button class="btn btn-sm btn-outline-primary px-4" data-bs-toggle="modal" data-bs-target="#annModal{{ $a->id }}" style="border-radius:10px;font-weight:600;">Read Full Story <i class="bi bi-arrow-right-short"></i></button>
    </div>
</div>

<div class="modal fade" id="annModal{{ $a->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content" style="border-radius:24px;border:none;overflow:hidden;">
        <div class="modal-header border-0 p-4 pb-0"><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body p-4 p-md-5 pt-0">
            <div class="text-center mb-4">
                <div class="stat-icon primary bg-opacity-10 mx-auto mb-3" style="width:60px;height:60px;font-size:1.75rem;"><i class="bi bi-megaphone"></i></div>
                <span class="announcement-chip {{ $a->category === 'lost_item' ? 'category-lost' : 'category-general' }}">
                    <i class="bi {{ $a->category === 'lost_item' ? 'bi-search' : 'bi-megaphone' }}"></i> {{ $a->category_label }}
                </span>
                <h2 class="fw-bold text-dark h3 px-md-5 mt-2">{{ $a->title }}</h2>
                <div class="text-muted small mt-2"><i class="bi bi-person"></i> {{ $a->author->fullname ?? 'SSC Admin' }} &bull; <i class="bi bi-calendar3"></i> {{ $a->created_at?->format('F d, Y') }}</div>
            </div>
            <hr class="opacity-10 my-4">
            @if($a->image_path)
            <img src="{{ \App\Helpers\SscHelper::getUploadUrl($a->image_path) }}" alt="" style="width:100%; max-height:360px; border-radius:16px; margin-bottom:24px; object-fit:cover;">
            @endif
            <div style="font-size:1.05rem;line-height:1.9;color:var(--slate-700);">{!! nl2br(e($a->content)) !!}</div>
            @if($a->project_id && $a->proposal?->completion_proof)
            <div class="mt-5 p-4 rounded-4 bg-success bg-opacity-10 border border-success border-opacity-20 d-flex justify-content-between align-items-center">
                <div><h6 class="fw-bold text-success mb-1"><i class="bi bi-shield-check"></i> Verified Audit Proof</h6><div class="text-muted small">Official receipt available.</div></div>
                <a href="{{ \App\Helpers\SscHelper::getUploadUrl($a->proposal->completion_proof) }}" target="_blank" class="btn btn-success px-4" style="border-radius:10px;font-weight:600;"><i class="bi bi-receipt"></i> View Receipt</a>
            </div>
            @endif

            @if($a->category === 'lost_item')
            <hr class="opacity-10 my-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-chat-dots"></i> Comments ({{ $a->comments->count() }})</h6>

            <form method="POST" action="{{ route('student.announcements.comment', $a) }}" class="mb-4">
                @csrf
                <div class="mb-2"><textarea name="comment" class="form-control-custom" rows="2" placeholder="Found this item, or know whose it is? Leave a comment..." required style="border-radius:12px;"></textarea></div>
                <button type="submit" class="btn-primary-custom px-4" style="padding:8px 18px;font-size:0.85rem;">Post Comment <i class="bi bi-send ms-1"></i></button>
            </form>

            <div class="d-flex flex-column gap-3">
                @forelse($a->comments as $c)
                <div class="d-flex gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0 bg-light text-muted" style="width:38px;height:38px;font-size:1.1rem;">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <div class="flex-fill">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-dark small"><i class="bi bi-person-fill-lock"></i> Anonymous Student</span>
                            <span class="text-muted small" style="font-size:0.7rem;">{{ $c->created_at?->diffForHumans() }}</span>
                        </div>
                        <div class="p-3 bg-light rounded-4 small text-dark" style="line-height:1.6;">{!! nl2br(e($c->comment)) !!}</div>
                    </div>
                </div>
                @empty
                <div class="text-center py-3 text-muted small">No comments yet. Be the first to help!</div>
                @endforelse
            </div>
            @endif
        </div>
        <div class="modal-footer border-0 p-4 bg-light bg-opacity-50"><button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal" style="border-radius:12px;padding:12px;font-weight:600;">Close Announcement</button></div>
    </div></div>
</div>
</div>
@empty
<div class="col-12">
<div class="card text-center" style="border-radius:var(--radius); border:1px solid var(--slate-200); box-shadow:none;">
    <div class="card-body-custom py-5">
        <div class="stat-icon primary bg-opacity-10 mx-auto mb-3" style="width:56px; height:56px; font-size:1.6rem;"><i class="bi bi-megaphone"></i></div>
        <div class="fw-bold" style="color:var(--slate-800);">{{ $category ? 'No ' . (\App\Models\Announcement::CATEGORIES[$category] ?? 'matching') . ' announcements yet.' : 'No announcements yet.' }}</div>
        <p class="text-muted mb-0 mt-1" style="font-size:0.85rem;">Check back later for updates from the SSC.</p>
    </div>
</div>
</div>
@endforelse
</div>
@endsection
