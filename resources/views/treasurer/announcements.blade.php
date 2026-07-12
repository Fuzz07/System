@extends('layouts.app')

@section('sidebar-nav') @include('partials.sidebar-treasurer') @endsection

@section('content')
<div class="page-header mb-4">
  <div>
    <h1>Announcements</h1>
    <p>Official announcements from the Supreme Student Council</p>
  </div>
</div>

@forelse ($announcements as $a)
  <div class="announcement-card transition hover-shadow mb-4" style="border-radius:18px; padding:28px; background: #fff; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <div class="d-flex justify-content-between align-items-start mb-3">
      <h2 style="font-size:1.1rem; font-weight:700; color:var(--navy); margin-bottom:0; flex:1;">{{ $a->title }}</h2>
      <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10 px-3 py-2" style="font-size:0.7rem; border-radius: 12px;">
        <i class="bi bi-calendar3"></i> {{ $a->created_at->format('M d, Y') }}
      </span>
    </div>

    <div style="font-size:.9rem; color:#4a5568; line-height:1.7; margin-bottom:20px;">
      {{ Str::limit($a->content, 250) }}
    </div>

    <div class="d-flex justify-content-between align-items-center">
      <div style="font-size:.75rem; color:#a0aec0; display:flex; align-items:center; gap:12px;">
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
            <h2 class="fw-bold text-dark h3 px-md-5">{{ $a->title }}</h2>
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
                <a href="{{ asset('storage/' . $a->proposal->completion_proof) }}" target="_blank" class="btn btn-outline-info" style="border-radius: 12px; font-weight: 600;">
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
  <div class="text-center py-5 text-muted">
    <i class="bi bi-megaphone" style="font-size:3rem; opacity:.2;"></i>
    <div class="mt-3">No announcements yet.</div>
  </div>
@endforelse
@endsection
