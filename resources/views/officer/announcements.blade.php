@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-officer') @endsection

@section('content')
<div class="page-header"><div><h1>Announcements</h1><p>Post and manage SSC announcements</p></div>
    <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#annModal"><i class="bi bi-megaphone"></i> New Announcement</button>
</div>

<div class="d-flex gap-2 mb-3">
    <a href="{{ route('officer.announcements') }}" class="btn btn-sm {{ !$category ? 'btn-primary-custom' : 'btn-outline-secondary' }}" style="border-radius:20px; font-size:0.8rem; padding:6px 16px;">All</a>
    @foreach(\App\Models\Announcement::CATEGORIES as $value => $label)
    <a href="{{ route('officer.announcements', ['category' => $value]) }}" class="btn btn-sm {{ $category === $value ? 'btn-primary-custom' : 'btn-outline-secondary' }}" style="border-radius:20px; font-size:0.8rem; padding:6px 16px;">{{ $label }}</a>
    @endforeach
</div>

@forelse($announcements as $a)
<div class="announcement-card d-flex justify-content-between align-items-start">
    <div style="flex:1;">
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge {{ $a->category === 'lost_item' ? 'bg-warning text-dark' : 'bg-info text-dark' }}" style="font-size:0.62rem; text-transform:uppercase; font-weight:700;">{{ $a->category_label }}</span>
            <div style="font-weight:700;font-size:.95rem;color:var(--navy-900);">{{ $a->title }}</div>
        </div>
        @if($a->image_path)
        <img src="{{ \App\Helpers\SscHelper::getUploadUrl($a->image_path) }}" alt="" style="max-width:100%; max-height:220px; border-radius:10px; margin-bottom:8px; object-fit:cover;">
        @endif
        <div style="font-size:.85rem;color:#4a5568;margin-bottom:8px;line-height:1.7;">{!! nl2br(e($a->content)) !!}</div>
        <div style="font-size:.72rem;color:#a0aec0;"><i class="bi bi-person"></i> {{ $a->author->fullname ?? 'SSC' }} &bull; <i class="bi bi-clock"></i> {{ $a->created_at?->diffForHumans() }}</div>
    </div>
    @if($a->created_by === Auth::id())
    <div class="d-flex gap-2 ms-3">
        <button type="button" class="btn btn-outline-secondary btn-sm" style="font-size:.72rem;" data-bs-toggle="modal" data-bs-target="#editAnnModal{{ $a->id }}"><i class="bi bi-pencil"></i></button>
        <form method="POST" action="{{ route('officer.announcements.destroy', $a) }}" onsubmit="return confirm('Delete this announcement?')">@csrf @method('DELETE')
            <button class="btn btn-outline-danger btn-sm" style="font-size:.72rem;"><i class="bi bi-trash"></i></button>
        </form>
    </div>
    @endif
</div>

{{-- Edit Announcement Modal --}}
<div class="modal fade" id="editAnnModal{{ $a->id }}" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content" style="border-radius:var(--radius);border:none;">
    <div class="modal-header modal-header-custom"><h5 class="modal-title" style="font-weight:700;"><i class="bi bi-pencil"></i> Edit Announcement</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST" action="{{ route('officer.announcements.update', $a) }}" enctype="multipart/form-data">@csrf @method('PUT')
        <div class="modal-body p-4">
            <div class="mb-3"><label class="form-label-custom">Title <span class="text-danger">*</span></label><input type="text" name="title" class="form-control-custom" value="{{ $a->title }}" required></div>
            <div class="mb-3">
                <label class="form-label-custom">Category</label>
                <select name="category" class="form-control-custom">
                    @foreach(\App\Models\Announcement::CATEGORIES as $value => $label)
                    <option value="{{ $value }}" {{ $a->category === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3"><label class="form-label-custom">Content <span class="text-danger">*</span></label><textarea name="content" class="form-control-custom" rows="5" required style="resize:vertical;">{{ $a->content }}</textarea></div>
            <div class="mb-3">
                <label class="form-label-custom">Photo (optional)</label>
                @if($a->image_path)
                <div class="d-flex align-items-center gap-3 mb-2">
                    <img src="{{ \App\Helpers\SscHelper::getUploadUrl($a->image_path) }}" alt="" style="max-height:80px; border-radius:8px;">
                    <label style="font-size:0.8rem; color:#64748b;"><input type="checkbox" name="remove_image" value="1"> Remove current photo</label>
                </div>
                @endif
                <input type="file" name="image" class="form-control-custom" accept="image/*">
                <div class="form-text" style="font-size:0.75rem;color:#94a3b8;">Upload a new photo to replace the current one. JPG, PNG or WEBP, up to 5MB.</div>
            </div>
        </div>
        <div class="modal-footer border-0 pt-0"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn-primary-custom"><i class="bi bi-check2"></i> Save Changes</button></div>
    </form>
</div></div></div>
@empty
<div class="text-center py-5 text-muted"><i class="bi bi-megaphone" style="font-size:2rem;opacity:.2;"></i><div class="mt-2">No announcements yet.</div></div>
@endforelse

<div class="modal fade" id="annModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content" style="border-radius:var(--radius);border:none;">
    <div class="modal-header modal-header-custom"><h5 class="modal-title" style="font-weight:700;"><i class="bi bi-megaphone"></i> New Announcement</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST" action="{{ route('officer.announcements.store') }}" enctype="multipart/form-data">@csrf
        <div class="modal-body p-4">
            <div class="mb-3"><label class="form-label-custom">Title <span class="text-danger">*</span></label><input type="text" name="title" class="form-control-custom" required></div>
            <div class="mb-3">
                <label class="form-label-custom">Category</label>
                <select name="category" class="form-control-custom">
                    @foreach(\App\Models\Announcement::CATEGORIES as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3"><label class="form-label-custom">Content <span class="text-danger">*</span></label><textarea name="content" class="form-control-custom" rows="5" required style="resize:vertical;"></textarea></div>
            <div class="mb-3"><label class="form-label-custom">Photo (optional)</label><input type="file" name="image" class="form-control-custom" accept="image/*"><div class="form-text" style="font-size:0.75rem;color:#94a3b8;">JPG, PNG or WEBP, up to 5MB.</div></div>
        </div>
        <div class="modal-footer border-0 pt-0"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn-primary-custom"><i class="bi bi-send"></i> Post Announcement</button></div>
    </form>
</div></div></div>
@endsection
