@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-officer') @endsection

@section('content')
<div class="page-header"><div><h1>Announcements</h1><p>Post and manage SSC announcements</p></div>
    <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#annModal"><i class="bi bi-megaphone"></i> New Announcement</button>
</div>

@forelse($announcements as $a)
<div class="announcement-card d-flex justify-content-between align-items-start">
    <div style="flex:1;">
        <div style="font-weight:700;font-size:.95rem;color:var(--navy-900);margin-bottom:6px;">{{ $a->title }}</div>
        <div style="font-size:.85rem;color:#4a5568;margin-bottom:8px;line-height:1.7;">{!! nl2br(e($a->content)) !!}</div>
        <div style="font-size:.72rem;color:#a0aec0;"><i class="bi bi-person"></i> {{ $a->author->fullname ?? 'SSC' }} &bull; <i class="bi bi-clock"></i> {{ $a->created_at?->diffForHumans() }}</div>
    </div>
    @if($a->created_by === Auth::id())
    <form method="POST" action="{{ route('officer.announcements.destroy', $a) }}" class="ms-3" onsubmit="return confirm('Delete this announcement?')">@csrf @method('DELETE')
        <button class="btn btn-outline-danger btn-sm" style="font-size:.72rem;"><i class="bi bi-trash"></i></button>
    </form>
    @endif
</div>
@empty
<div class="text-center py-5 text-muted"><i class="bi bi-megaphone" style="font-size:2rem;opacity:.2;"></i><div class="mt-2">No announcements yet.</div></div>
@endforelse

<div class="modal fade" id="annModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content" style="border-radius:var(--radius);border:none;">
    <div class="modal-header modal-header-custom"><h5 class="modal-title" style="font-weight:700;"><i class="bi bi-megaphone"></i> New Announcement</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST" action="{{ route('officer.announcements.store') }}">@csrf
        <div class="modal-body p-4">
            <div class="mb-3"><label class="form-label-custom">Title <span class="text-danger">*</span></label><input type="text" name="title" class="form-control-custom" required></div>
            <div class="mb-3"><label class="form-label-custom">Content <span class="text-danger">*</span></label><textarea name="content" class="form-control-custom" rows="5" required style="resize:vertical;"></textarea></div>
        </div>
        <div class="modal-footer border-0 pt-0"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn-primary-custom"><i class="bi bi-send"></i> Post Announcement</button></div>
    </form>
</div></div></div>
@endsection
