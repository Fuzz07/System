@extends('layouts.app')

@section('sidebar-nav')
    <a href="{{ route('student.overview') }}" class="nav-item active">
        <i class="bi bi-house-door"></i>
        <span>Overview</span>
    </a>
    <a href="{{ route('student.proposals') }}" class="nav-item">
        <i class="bi bi-file-text"></i>
        <span>Proposals</span>
    </a>
    <a href="{{ route('student.announcements') }}" class="nav-item">
        <i class="bi bi-megaphone"></i>
        <span>Announcements</span>
    </a>
    <a href="{{ route('student.officers') }}" class="nav-item">
        <i class="bi bi-people"></i>
        <span>Officers</span>
    </a>
    <a href="{{ route('student.feedback') }}" class="nav-item">
        <i class="bi bi-chat-dots"></i>
        <span>Feedback</span>
    </a>
    <a href="{{ route('student.voting') }}" class="nav-item">
        <i class="bi bi-ballot"></i>
        <span>Voting</span>
    </a>
    <a href="{{ route('student.candidacy') }}" class="nav-item">
        <i class="bi bi-award"></i>
        <span>Candidacy</span>
    </a>
    <a href="{{ route('student.enrollment.index') }}" class="nav-item">
        <i class="bi bi-cash-stack"></i>
        <span>Enrollment</span>
    </a>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-12">
            <!-- Welcome Card -->
            <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <div class="card-body p-4">
                    <h2 class="card-title mb-2">Welcome back, {{ Auth::user()->fullname }}! 👋</h2>
                    <p class="card-text mb-0">Stay updated with the latest announcements, proposals, and SSC activities.</p>
                </div>
            </div>

            <!-- Announcements Section -->
            @if($announcements->count() > 0)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center" style="border-radius: var(--radius-md) var(--radius-md) 0 0;">
                    <h5 class="mb-0"><i class="bi bi-megaphone text-warning me-2"></i>Latest Announcements</h5>
                    <a href="{{ route('student.announcements') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    @foreach($announcements as $announcement)
                    <div class="p-3 border-bottom {{ $loop->last ? 'border-0' : '' }}">
                        <h6 class="mb-1 fw-600">{{ $announcement->title }}</h6>
                        <p class="text-muted small mb-2">{{ Str::limit($announcement->content, 100) }}</p>
                        <small class="text-muted">
                            <i class="bi bi-calendar2"></i> {{ $announcement->created_at->format('M d, Y') }}
                            @if($announcement->officer)
                            • By {{ $announcement->officer->fullname }}
                            @endif
                        </small>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Proposals Section -->
            @if($pendingProposals->count() > 0)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-file-text text-info me-2"></i>Active Proposals</h5>
                    <a href="{{ route('student.proposals') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    @foreach($pendingProposals as $proposal)
                    <div class="p-3 border-bottom {{ $loop->last ? 'border-0' : '' }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-600">
                                    <a href="{{ route('student.proposal.show', $proposal) }}" class="text-decoration-none">
                                        {{ $proposal->title }}
                                    </a>
                                </h6>
                                <p class="text-muted small mb-2">{{ Str::limit($proposal->description, 80) }}</p>
                                <small class="text-muted">
                                    <i class="bi bi-calendar2"></i> {{ $proposal->created_at->format('M d, Y') }}
                                </small>
                            </div>
                            <span class="badge bg-{{ $proposal->status === 'Approved' ? 'success' : 'warning' }} ms-2">
                                {{ $proposal->status }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Candidacy Status -->
            @if($activeSy)
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="bi bi-award text-success me-2"></i>Candidacy Status</h5>
                </div>
                <div class="card-body">
                    @if($activeCandidacy)
                        <div class="alert alert-info mb-0 d-flex align-items-center">
                            <i class="bi bi-info-circle me-2"></i>
                            <div>
                                <strong>Active Application:</strong> {{ $activeCandidacy->position }}
                                <br>
                                <small>Status: <span class="badge bg-{{ $activeCandidacy->status === 'approved' ? 'success' : ($activeCandidacy->status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($activeCandidacy->status) }}</span></small>
                            </div>
                        </div>
                    @else
                        <p class="text-muted mb-0">You haven't submitted a candidacy application for the current school year.</p>
                        @if($activeSy->candidacy_open)
                        <a href="{{ route('student.candidacy') }}" class="btn btn-sm btn-primary mt-2">
                            <i class="bi bi-plus"></i> Apply for Candidacy
                        </a>
                        @endif
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
    .card {
        border-radius: var(--radius-md, 8px);
    }
    
    .nav-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 15px;
        text-decoration: none;
        color: #666;
        border-radius: 6px;
        margin-bottom: 5px;
        transition: all 0.2s;
    }
    
    .nav-item:hover,
    .nav-item.active {
        background-color: #f0f4ff;
        color: #667eea;
    }
</style>
@endsection
