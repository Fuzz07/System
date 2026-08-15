@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-officer') @endsection

@section('content')
<div class="page-header"><div><h1>Announcements</h1><p>Post and manage SSC announcements</p></div>
    <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#annModal"><i class="bi bi-megaphone"></i> New Announcement</button>
</div>

<div class="category-filter-bar">
    <a href="{{ route('officer.announcements') }}" class="{{ !$category ? 'active' : '' }}"><i class="bi bi-grid"></i> All</a>
    <a href="{{ route('officer.announcements', ['category' => 'general']) }}" class="{{ $category === 'general' ? 'active' : '' }}"><i class="bi bi-megaphone"></i> General</a>
    <a href="{{ route('officer.announcements', ['category' => 'lost_item']) }}" class="{{ $category === 'lost_item' ? 'active' : '' }}"><i class="bi bi-search"></i> Lost &amp; Found</a>
</div>

<div class="row g-4">
@forelse($announcements as $a)
<div class="col-12 col-md-6">
<div class="announcement-card d-flex justify-content-between align-items-start gap-3 h-100 {{ $a->category === 'lost_item' ? 'category-lost' : '' }}">
    <div style="flex:1; min-width:0;">
        <span class="announcement-chip {{ $a->category === 'lost_item' ? 'category-lost' : 'category-general' }}">
            <i class="bi {{ $a->category === 'lost_item' ? 'bi-search' : 'bi-megaphone' }}"></i> {{ $a->category_label }}
        </span>
        <div class="announcement-title" style="font-size:0.98rem; margin:8px 0 10px;">{{ $a->title }}</div>
        @if($a->image_path)
        <img src="{{ \App\Helpers\SscHelper::getUploadUrl($a->image_path) }}" alt="" class="announcement-image" style="max-height:220px;">
        @endif
        <div class="announcement-body" style="margin-bottom:12px;">{!! nl2br(e($a->content)) !!}</div>
        <div class="announcement-meta" style="border-top:none; padding-top:0;">
            <span><i class="bi bi-person"></i> {{ $a->author->fullname ?? 'SSC' }}</span>
            <span><i class="bi bi-clock"></i> {{ $a->created_at?->diffForHumans() }}</span>
        </div>
    </div>
    @if($a->created_by === Auth::id())
    <div class="announcement-actions">
        <button type="button" class="btn-icon-sm" data-bs-toggle="modal" data-bs-target="#editAnnModal{{ $a->id }}" title="Edit"><i class="bi bi-pencil"></i></button>
        <form method="POST" action="{{ route('officer.announcements.destroy', $a) }}" onsubmit="return confirm('Delete this announcement?')">@csrf @method('DELETE')
            <button class="btn-icon-sm danger" title="Delete"><i class="bi bi-trash"></i></button>
        </form>
    </div>
    @endif
</div>

{{-- Edit Announcement Modal --}}
<div class="modal fade" id="editAnnModal{{ $a->id }}" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content" style="border-radius:var(--radius);border:none;">
    <div class="modal-header modal-header-custom"><h5 class="modal-title" style="font-weight:700;"><i class="bi bi-pencil"></i> Edit Announcement</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST" action="{{ route('officer.announcements.update', $a) }}" enctype="multipart/form-data">@csrf @method('PUT')
        <div class="modal-body p-4">
            <div class="mb-4"><label class="form-label-custom">Title <span class="text-danger">*</span></label><input type="text" name="title" class="form-control-custom" value="{{ $a->title }}" required></div>
            <div class="mb-4">
                <label class="form-label-custom">Category</label>
                <div class="category-picker">
                    <label class="category-option">
                        <input type="radio" name="category" value="general" {{ $a->category !== 'lost_item' ? 'checked' : '' }}>
                        <span class="category-pill"><i class="bi bi-megaphone"></i> General</span>
                    </label>
                    <label class="category-option">
                        <input type="radio" name="category" value="lost_item" {{ $a->category === 'lost_item' ? 'checked' : '' }}>
                        <span class="category-pill pill-lost"><i class="bi bi-search"></i> Lost &amp; Found</span>
                    </label>
                </div>
            </div>
            <div class="mb-4"><label class="form-label-custom">Content <span class="text-danger">*</span></label><textarea name="content" class="form-control-custom" rows="5" required style="resize:vertical;">{{ $a->content }}</textarea></div>
            <div class="mb-2 photo-field">
                <label class="form-label-custom">Photo <span class="text-muted fw-normal">(optional)</span></label>
                <label class="upload-dropzone">
                    <input type="file" name="image" accept="image/*" hidden onchange="sscPreviewImage(this)">
                    <div class="upload-placeholder" style="{{ $a->image_path ? 'display:none;' : '' }}">
                        <i class="bi bi-cloud-arrow-up"></i>
                        <div class="upload-text">Click to upload a photo</div>
                        <div class="upload-hint">JPG, PNG or WEBP — up to 5MB</div>
                    </div>
                    <img class="upload-preview" src="{{ $a->image_path ? \App\Helpers\SscHelper::getUploadUrl($a->image_path) : '' }}" style="{{ $a->image_path ? 'display:inline-block;' : 'display:none;' }}">
                </label>
                @if($a->image_path)
                <label class="upload-remove-label">
                    <input type="checkbox" name="remove_image" value="1" onchange="sscToggleRemove(this)"> Remove current photo
                </label>
                @endif
            </div>
        </div>
        <div class="modal-footer border-0 pt-0"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn-primary-custom"><i class="bi bi-check2"></i> Save Changes</button></div>
    </form>
</div></div></div>
</div>
@empty
<div class="col-12">
<div class="card text-center" style="border-radius:var(--radius); border:1px solid var(--slate-200); box-shadow:none;">
    <div class="card-body-custom py-5">
        <div class="stat-icon primary bg-opacity-10 mx-auto mb-3" style="width:56px; height:56px; font-size:1.6rem;"><i class="bi bi-megaphone"></i></div>
        <div class="fw-bold" style="color:var(--slate-800);">{{ $category ? 'No ' . (\App\Models\Announcement::CATEGORIES[$category] ?? 'matching') . ' announcements yet.' : 'No announcements yet.' }}</div>
    </div>
</div>
</div>
@endforelse
</div>

<div class="modal fade" id="annModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content" style="border-radius:var(--radius);border:none;">
    <div class="modal-header modal-header-custom"><h5 class="modal-title" style="font-weight:700;"><i class="bi bi-megaphone"></i> New Announcement</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST" action="{{ route('officer.announcements.store') }}" enctype="multipart/form-data">@csrf
        <div class="modal-body p-4">
            <div class="mb-4"><label class="form-label-custom">Title <span class="text-danger">*</span></label><input type="text" name="title" class="form-control-custom" required></div>
            <div class="mb-4">
                <label class="form-label-custom">Category</label>
                <div class="category-picker">
                    <label class="category-option">
                        <input type="radio" name="category" value="general" checked>
                        <span class="category-pill"><i class="bi bi-megaphone"></i> General</span>
                    </label>
                    <label class="category-option">
                        <input type="radio" name="category" value="lost_item">
                        <span class="category-pill pill-lost"><i class="bi bi-search"></i> Lost &amp; Found</span>
                    </label>
                </div>
            </div>
            <div class="mb-4"><label class="form-label-custom">Content <span class="text-danger">*</span></label><textarea name="content" class="form-control-custom" rows="5" required style="resize:vertical;"></textarea></div>
            <div class="mb-2 photo-field">
                <label class="form-label-custom">Photo <span class="text-muted fw-normal">(optional)</span></label>
                <label class="upload-dropzone">
                    <input type="file" name="image" accept="image/*" hidden onchange="sscPreviewImage(this)">
                    <div class="upload-placeholder">
                        <i class="bi bi-cloud-arrow-up"></i>
                        <div class="upload-text">Click to upload a photo</div>
                        <div class="upload-hint">JPG, PNG or WEBP — up to 5MB</div>
                    </div>
                    <img class="upload-preview" src="" style="display:none;">
                </label>
            </div>
        </div>
        <div class="modal-footer border-0 pt-0"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn-primary-custom"><i class="bi bi-send"></i> Post Announcement</button></div>
    </form>
</div></div></div>

<script>
    function sscPreviewImage(input) {
        const wrapper = input.closest('.upload-dropzone');
        const placeholder = wrapper.querySelector('.upload-placeholder');
        const preview = wrapper.querySelector('.upload-preview');
        const file = input.files && input.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function (e) {
            preview.src = e.target.result;
            preview.style.display = 'inline-block';
            preview.style.opacity = '1';
            if (placeholder) placeholder.style.display = 'none';
        };
        reader.readAsDataURL(file);
    }

    function sscToggleRemove(checkbox) {
        const wrapper = checkbox.closest('.photo-field');
        const preview = wrapper ? wrapper.querySelector('.upload-preview') : null;
        if (preview) preview.style.opacity = checkbox.checked ? '0.35' : '1';
    }

    document.getElementById('annModal')?.addEventListener('hidden.bs.modal', function () {
        const form = this.querySelector('form');
        form.reset();
        const preview = this.querySelector('.upload-preview');
        const placeholder = this.querySelector('.upload-placeholder');
        if (preview) { preview.style.display = 'none'; preview.src = ''; }
        if (placeholder) placeholder.style.display = '';
    });
</script>
@endsection
