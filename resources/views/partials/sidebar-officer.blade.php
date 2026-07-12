{{-- Officer Sidebar Navigation --}}
<div class="nav-section-label">Main</div>
<a href="{{ route('officer.dashboard') }}" class="nav-link {{ request()->routeIs('officer.dashboard') ? 'active' : '' }}">
    <span class="nav-icon"><i class="bi bi-grid-1x2"></i></span> Dashboard
</a>

<div class="nav-section-label">Management</div>
<a href="{{ route('officer.proposals') }}" class="nav-link {{ request()->routeIs('officer.proposals') ? 'active' : '' }}">
    <span class="nav-icon"><i class="bi bi-file-earmark-text"></i></span> My Proposals
</a>
<a href="{{ route('officer.expenses') }}" class="nav-link {{ request()->routeIs('officer.expenses') ? 'active' : '' }}">
    <span class="nav-icon"><i class="bi bi-receipt"></i></span> My Expenses
</a>
<a href="{{ route('officer.announcements') }}" class="nav-link {{ request()->routeIs('officer.announcements') ? 'active' : '' }}">
    <span class="nav-icon"><i class="bi bi-megaphone"></i></span> Announcements
</a>
<a href="{{ route('officer.liquidation') }}" class="nav-link {{ request()->routeIs('officer.liquidation') ? 'active' : '' }}">
    <span class="nav-icon"><i class="bi bi-folder-check"></i></span> Liquidation
</a>
