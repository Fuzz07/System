@extends('layouts.app')

@section('sidebar-nav') @include('partials.sidebar-treasurer') @endsection

@section('content')
<div class="page-header mb-4">
  <div>
    <h1>Announcements</h1>
    <p>Official announcements from the Supreme Student Council</p>
  </div>
</div>

<div class="category-filter-bar">
    <a href="{{ route('treasurer.announcements') }}" class="{{ !$category ? 'active' : '' }}"><i class="bi bi-grid"></i> All</a>
    <a href="{{ route('treasurer.announcements', ['category' => 'general']) }}" class="{{ $category === 'general' ? 'active' : '' }}"><i class="bi bi-megaphone"></i> General</a>
    <a href="{{ route('treasurer.announcements', ['category' => 'lost_item']) }}" class="{{ $category === 'lost_item' ? 'active' : '' }}"><i class="bi bi-search"></i> Lost &amp; Found</a>
</div>

@forelse ($announcements as $a)
  <div class="announcement-card {{ $a->category === 'lost_item' ? 'category-lost' : '' }}">
    <div class="d-flex justify-content-between align-items-start gap-3">
      <div style="flex:1; min-width:0;">
        <span class="announcement-chip {{ $a->category === 'lost_item' ? 'category-lost' : 'category-general' }}">
            <i class="bi {{ $a->category === 'lost_item' ? 'bi-search' : 'bi-megaphone' }}"></i> {{ $a->category_label }}
        </span>
        <h2 class="announcement-title" style="font-size:1.1rem;">{{ $a->title }}</h2>
      </div>
      <span class="announcement-chip role" style="flex-shrink:0;">
        <i class="bi bi-calendar3"></i> {{ $a->created_at->format('M d, Y') }}
      </span>
    </div>

    <div class="announcement-body">
      {{ Str::limit($a->content, 250) }}
    </div>

    <div class="d-flex justify-content-between align-items-center">
      <div style="font-size:.78rem; color:var(--slate-400); display:flex; align-items:center; gap:14px;">
        <span><i class="bi bi-person-circle"></i> {{ $a->author->fullname ?? 'SSC Admin' }}</span>
        <span><i class="bi bi-clock"></i> {!! \App\Helpers\SscHelper::timeAgo($a->created_at) !!}</span>
      </div>
      <button class="btn btn-sm btn-outline-primary px-4" data-bs-toggle="modal" data-bs-target="#annModal{{ $a->id }}"
        style="border-radius:10px; font-weight:600;">
        Read Full Story <i class="bi bi-arrow-right-short"></i>
      </button>
    </div>
  </div>

  <!-- Announcement Modal -->
  <div class="modal fade" id="annModal{{ $a->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content" style="border-radius:24px; border:none; overflow:hidden;">
        <div class="modal-header border-0 p-4 pb-0">
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4 p-md-5 pt-0">
          <div class="text-center mb-4">
            <div class="stat-icon primary bg-opacity-10 mx-auto mb-3" style="width:60px; height:60px; font-size:1.75rem;">
              <i class="bi bi-megaphone"></i></div>
            <span class="announcement-chip {{ $a->category === 'lost_item' ? 'category-lost' : 'category-general' }}">
                <i class="bi {{ $a->category === 'lost_item' ? 'bi-search' : 'bi-megaphone' }}"></i> {{ $a->category_label }}
            </span>
            <h2 class="fw-bold text-dark h3 px-md-5 mt-2">{{ $a->title }}</h2>
            <div class="text-muted small mt-2">
              <i class="bi bi-person"></i> {{ $a->author->fullname ?? 'SSC Admin' }} &bull;
              <i class="bi bi-calendar3"></i> {{ $a->created_at->format('F d, Y') }}
            </div>
          </div>
          <hr class="opacity-10 my-4">
          <div style="font-size:1.05rem; line-height:1.9; color:var(--slate-700);">
            {!! nl2br(e($a->content)) !!}
          </div>

          @if ($a->project_id && $a->proposal)
            <hr class="opacity-10 my-4">
            @if ($a->proposal->completion_proof)
              <div class="text-center mt-3">
                <a href="{{ \App\Helpers\SscHelper::getUploadUrl($a->proposal->completion_proof) }}" target="_blank" class="btn btn-outline-info" style="border-radius: 12px; font-weight: 600;">
                  <i class="bi bi-receipt"></i> View Completion Receipt
                </a>
              </div>
            @endif
          @endif
        </div>
        <div class="modal-footer border-0 p-4 bg-light bg-opacity-50">
          <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal"
            style="border-radius:12px; padding:12px; font-weight:600;">Close Announcement</button>
        </div>
      </div>
    </div>
  </div>
@empty
  <div class="card text-center" style="border-radius:var(--radius); border:1px solid var(--slate-200); box-shadow:none;">
    <div class="card-body-custom py-5">
        <div class="stat-icon primary bg-opacity-10 mx-auto mb-3" style="width:56px; height:56px; font-size:1.6rem;"><i class="bi bi-megaphone"></i></div>
        <div class="fw-bold" style="color:var(--slate-800);">{{ $category ? 'No ' . (\App\Models\Announcement::CATEGORIES[$category] ?? 'matching') . ' announcements yet.' : 'No announcements yet.' }}</div>
    </div>
  </div>
@endforelse
@endsection
