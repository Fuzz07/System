{{-- Dean Sidebar Navigation --}}
<div class="nav-section-label">Main</div>
<a href="{{ route('dean.dashboard') }}" class="nav-link {{ request()->routeIs('dean.dashboard') ? 'active' : '' }}">
    <span class="nav-icon"><i class="bi bi-grid-1x2"></i></span> Dashboard
</a>

<div class="nav-section-label">Department Selection</div>
<a href="{{ route('dean.dashboard') }}" class="nav-link {{ request()->routeIs('dean.dashboard') ? 'active' : '' }}">
    <span class="nav-icon"><i class="bi bi-person-check"></i></span> Candidate Voting
</a>
<a href="{{ route('dean.election.results') }}" class="nav-link {{ request()->routeIs('dean.election.results') ? 'active' : '' }}">
    <span class="nav-icon"><i class="bi bi-bar-chart"></i></span> Election Results
</a>
