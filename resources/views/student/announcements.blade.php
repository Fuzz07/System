@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-student') @endsection

@section('content')
<div class="page-header"><div><h1>Announcements</h1><p>Official announcements from the Supreme Student Council</p></div></div>

<div class="d-flex gap-2 mb-4">
    <a href="{{ route('student.announcements') }}" class="btn btn-sm {{ !$category ? 'btn-primary-custom' : 'btn-outline-secondary' }}" style="border-radius:20px; font-size:0.8rem; padding:6px 16px;">All</a>
    @foreach(\App\Models\Announcement::CATEGORIES as $value => $label)
    <a href="{{ route('student.announcements', ['category' => $value]) }}" class="btn btn-sm {{ $category === $value ? 'btn-primary-custom' : 'btn-outline-secondary' }}" style="border-radius:20px; font-size:0.8rem; padding:6px 16px;">{{ $label }}</a>
    @endforeach
</div>

@forelse($announcements as $a)
<div class="announcement-card transition hover-shadow" style="border-radius:18px;padding:28px;">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div style="flex:1;">
            <span class="badge {{ $a->category === 'lost_item' ? 'bg-warning text-dark' : 'bg-info text-dark' }}" style="font-size:0.65rem; text-transform:uppercase; font-weight:700; margin-bottom:8px;">{{ $a->category_label }}</span>
            <h2 style="font-size:1.1rem;font-weight:700;color:var(--navy-900);margin:6px 0 0;">{{ $a->title }}</h2>
        </div>
        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10 px-3 py-2" style="font-size:0.7rem;"><i class="bi bi-calendar3"></i> {{ $a->created_at?->format('M d, Y') }}</span>
    </div>
    @if($a->image_path)
    <img src="{{ \App\Helpers\SscHelper::getUploadUrl($a->image_path) }}" alt="" style="width:100%; max-height:260px; border-radius:14px; margin-bottom:16px; object-fit:cover;">
    @endif
    <div style="font-size:.9rem;color:#4a5568;line-height:1.7;margin-bottom:20px;">{!! nl2br(e(Str::limit($a->content, 250))) !!}</div>
    <div class="d-flex justify-content-between align-items-center">
        <div style="font-size:.75rem;color:#a0aec0;display:flex;align-items:center;gap:12px;">
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
                <span class="badge {{ $a->category === 'lost_item' ? 'bg-warning text-dark' : 'bg-info text-dark' }}" style="font-size:0.65rem; text-transform:uppercase; font-weight:700;">{{ $a->category_label }}</span>
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
@empty
<div class="text-center py-5 text-muted"><i class="bi bi-megaphone" style="font-size:3rem;opacity:.2;"></i><div class="mt-3">No announcements yet.</div></div>
@endforelse
@endsection
