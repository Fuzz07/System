{{-- Treasurer Sidebar Navigation --}}
<div class="nav-section-label">Main</div>
<a href="{{ route('treasurer.dashboard') }}" class="nav-link {{ request()->routeIs('treasurer.dashboard') ? 'active' : '' }}">
    <span class="nav-icon"><i class="bi bi-grid-1x2"></i></span> Dashboard
</a>

<div class="nav-section-label">Treasury</div>
<a href="{{ route('treasurer.release') }}" class="nav-link {{ request()->routeIs('treasurer.release') ? 'active' : '' }}">
    <span class="nav-icon"><i class="bi bi-cash-coin"></i></span> Release Budget
</a>
<a href="{{ route('treasurer.reports') }}" class="nav-link {{ request()->routeIs('treasurer.reports') ? 'active' : '' }}">
    <span class="nav-icon"><i class="bi bi-bar-chart-line"></i></span> Release Reports
</a>
<a href="{{ route('treasurer.announcements') }}" class="nav-link {{ request()->routeIs('treasurer.announcements') ? 'active' : '' }}">
    <span class="nav-icon"><i class="bi bi-megaphone"></i></span> Announcements
</a>
