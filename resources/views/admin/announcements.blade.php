@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-admin') @endsection

@section('content')
<div class="page-header">
    <div><h1>Announcements Management</h1><p>Post, view, and moderate all system announcements</p></div>
    <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#annModal"><i class="bi bi-megaphone"></i> Post Announcement</button>
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
                    <span style="font-weight:700; font-size:1.05rem; color:#0f172a;">{{ $a->title }}</span>
                </div>
                <div style="font-size:0.9rem; color:#475569; margin-bottom:12px; line-height:1.7; white-space:pre-line;">{!! nl2br(e($a->content)) !!}</div>
                <div style="font-size:0.75rem; color:#94a3b8;" class="d-flex align-items-center gap-3">
                    <span><i class="bi bi-person"></i> {{ $a->author->fullname ?? 'System' }}</span>
                    <span>&bull;</span>
                    <span><i class="bi bi-envelope"></i> {{ $a->author->email ?? '—' }}</span>
                    <span>&bull;</span>
                    <span><i class="bi bi-clock"></i> {{ $a->created_at?->diffForHumans() }}</span>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.announcements.destroy', $a) }}" class="ms-3" onsubmit="return confirm('Delete this announcement permanently?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-outline-danger btn-sm" style="font-size:0.75rem; border-radius:6px; padding:6px 10px;" title="Delete Announcement">
                    <i class="bi bi-trash"></i> Delete
                </button>
            </form>
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

{{-- Post Announcement Modal --}}
<div class="modal fade" id="annModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:var(--radius); border:none; box-shadow:0 10px 25px rgba(0,0,0,0.1);">
            <div class="modal-header modal-header-custom">
                <h5 class="modal-title" style="font-weight:700;"><i class="bi bi-megaphone"></i> Post New Announcement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1);"></button>
            </div>
            <form method="POST" action="{{ route('admin.announcements.store') }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label-custom">Announcement Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control-custom" placeholder="e.g. Notice on Semester Enrollment Fee Extensions" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-custom">Content Body <span class="text-danger">*</span></label>
                        <textarea name="content" class="form-control-custom" rows="8" placeholder="Enter complete announcement details here..." required style="resize:vertical;"></textarea>
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
@endsection
