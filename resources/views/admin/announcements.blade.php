@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-admin') @endsection

@section('content')
<div class="page-header">
    <div><h1>Announcements Management</h1><p>Post, view, and moderate all system announcements</p></div>
    <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#annModal"><i class="bi bi-megaphone"></i> Post Announcement</button>
</div>

<div class="d-flex gap-2 mb-3">
    <a href="{{ route('admin.announcements') }}" class="btn btn-sm {{ !$category ? 'btn-primary-custom' : 'btn-outline-secondary' }}" style="border-radius:20px; font-size:0.8rem; padding:6px 16px;">All</a>
    @foreach(\App\Models\Announcement::CATEGORIES as $value => $label)
    <a href="{{ route('admin.announcements', ['category' => $value]) }}" class="btn btn-sm {{ $category === $value ? 'btn-primary-custom' : 'btn-outline-secondary' }}" style="border-radius:20px; font-size:0.8rem; padding:6px 16px;">{{ $label }}</a>
    @endforeach
</div>

<div class="row">
    <div class="col-12">
        @forelse($announcements as $a)
        <div class="announcement-card d-flex justify-content-between align-items-start p-4 mb-3" style="background:#fff; border-radius:var(--radius); border:1px solid #e2e8f0; box-shadow:0 1px 3px rgba(0,0,0,0.02);">
            <div style="flex:1;">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge {{ $a->author?->role === 'admin' ? 'bg-primary' : 'bg-secondary' }}" style="font-size:0.65rem; text-transform:uppercase; font-weight:700;">
                        {{ $a->author?->role ?? 'SSC Admin' }}
                    </span>
                    <span class="badge {{ $a->category === 'lost_item' ? 'bg-warning text-dark' : 'bg-info text-dark' }}" style="font-size:0.65rem; text-transform:uppercase; font-weight:700;">
                        {{ $a->category_label }}
                    </span>
                    <span style="font-weight:700; font-size:1.05rem; color:#0f172a;">{{ $a->title }}</span>
                </div>
                @if($a->image_path)
                <img src="{{ \App\Helpers\SscHelper::getUploadUrl($a->image_path) }}" alt="" style="max-width:100%; max-height:280px; border-radius:10px; margin-bottom:12px; object-fit:cover;">
                @endif
                <div style="font-size:0.9rem; color:#475569; margin-bottom:12px; line-height:1.7; white-space:pre-line;">{!! nl2br(e($a->content)) !!}</div>
                <div style="font-size:0.75rem; color:#94a3b8;" class="d-flex align-items-center gap-3">
                    <span><i class="bi bi-person"></i> {{ $a->author->fullname ?? 'System' }}</span>
                    <span>&bull;</span>
                    <span><i class="bi bi-envelope"></i> {{ $a->author->email ?? '—' }}</span>
                    <span>&bull;</span>
                    <span><i class="bi bi-clock"></i> {{ $a->created_at?->diffForHumans() }}</span>
                </div>
            </div>
            <div class="d-flex gap-2 ms-3">
                <button type="button" class="btn btn-outline-secondary btn-sm" style="font-size:0.75rem; border-radius:6px; padding:6px 10px;" data-bs-toggle="modal" data-bs-target="#editAnnModal{{ $a->id }}" title="Edit Announcement">
                    <i class="bi bi-pencil"></i> Edit
                </button>
                <form method="POST" action="{{ route('admin.announcements.destroy', $a) }}" onsubmit="return confirm('Delete this announcement permanently?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm" style="font-size:0.75rem; border-radius:6px; padding:6px 10px;" title="Delete Announcement">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </form>
            </div>
        </div>

        {{-- Edit Announcement Modal --}}
        <div class="modal fade" id="editAnnModal{{ $a->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content" style="border-radius:var(--radius); border:none; box-shadow:0 10px 25px rgba(0,0,0,0.1);">
                    <div class="modal-header modal-header-custom">
                        <h5 class="modal-title" style="font-weight:700;"><i class="bi bi-pencil"></i> Edit Announcement</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1);"></button>
                    </div>
                    <form method="POST" action="{{ route('admin.announcements.update', $a) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="modal-body p-4">
                            <div class="mb-4">
                                <label class="form-label-custom">Announcement Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control-custom" value="{{ $a->title }}" required>
                            </div>
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
                            <div class="mb-4">
                                <label class="form-label-custom">Content Body <span class="text-danger">*</span></label>
                                <textarea name="content" class="form-control-custom" rows="7" required style="resize:vertical;">{{ $a->content }}</textarea>
                            </div>
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
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius:var(--radius-sm);">Cancel</button>
                            <button type="submit" class="btn-primary-custom" style="padding:10px 18px;"><i class="bi bi-check2"></i> Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="card p-5 text-center text-muted" style="border-radius:var(--radius); border:none;">
            <div class="card-body-custom">
                <i class="bi bi-megaphone" style="font-size:3rem; opacity:0.15; color:#475569;"></i>
                <div class="mt-3 fw-semibold">No announcements have been posted yet.</div>
                <p class="text-muted mb-0 mt-1" style="font-size:0.85rem;">Be the first to post an announcement using the button above.</p>
            </div>
        </div>
        @endforelse
    </div>
</div>

<div class="card mt-3" style="border:none; background:#fff; border-radius:var(--radius); box-shadow:0 1px 3px rgba(0,0,0,0.04);">
    {{ $announcements->withQueryString()->links('partials.pagination') }}
</div>

{{-- Post Announcement Modal --}}
<div class="modal fade" id="annModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:var(--radius); border:none; box-shadow:0 10px 25px rgba(0,0,0,0.1);">
            <div class="modal-header modal-header-custom">
                <h5 class="modal-title" style="font-weight:700;"><i class="bi bi-megaphone"></i> Post New Announcement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1);"></button>
            </div>
            <form method="POST" action="{{ route('admin.announcements.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label-custom">Announcement Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control-custom" placeholder="e.g. Notice on Semester Enrollment Fee Extensions" required>
                    </div>
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
                    <div class="mb-4">
                        <label class="form-label-custom">Content Body <span class="text-danger">*</span></label>
                        <textarea name="content" class="form-control-custom" rows="7" placeholder="Enter complete announcement details here..." required style="resize:vertical;"></textarea>
                    </div>
                    <div class="mb-2">
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
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius:var(--radius-sm);">Cancel</button>
                    <button type="submit" class="btn-primary-custom" style="padding:10px 18px;"><i class="bi bi-send"></i> Post Announcement</button>
                </div>
            </form>
        </div>
    </div>
</div>

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

    // Reset the "Post Announcement" modal to a blank state each time it opens,
    // so a previous selection doesn't linger into the next post.
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
