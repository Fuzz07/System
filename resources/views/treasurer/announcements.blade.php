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

<div class="row g-4">
@forelse ($announcements as $a)
  <div class="col-12 col-sm-6 col-xl-4">
    @include('partials.announcement-post-card', ['a' => $a, 'modal' => 'annModal' . $a->id])
    @include('partials.announcement-modal', ['a' => $a, 'modal' => 'annModal' . $a->id])
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
@endsection
