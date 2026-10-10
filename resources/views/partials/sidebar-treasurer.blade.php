{{-- Treasurer Sidebar Navigation --}}
<div class="nav-section-label">Main</div>
<a href="{{ route('treasurer.dashboard') }}" class="nav-link {{ request()->routeIs('treasurer.dashboard') ? 'active' : '' }}">
    <span class="nav-icon"><i class="bi bi-grid-1x2"></i></span> Dashboard
</a>

<div class="nav-section-label">Treasury</div>
<a href="{{ route('treasurer.release') }}" class="nav-link {{ request()->routeIs('treasurer.release') ? 'active' : '' }}">
    <span class="nav-icon"><i class="bi bi-cash-coin"></i></span> Release Budget
</a>
<a href="{{ route('treasurer.enrollment.payments') }}" class="nav-link {{ request()->routeIs('treasurer.enrollment.payments') ? 'active' : '' }}">
    <span class="nav-icon"><i class="bi bi-cash-stack"></i></span> Contribution Fees
</a>
<a href="{{ route('treasurer.cashbook') }}" class="nav-link {{ request()->routeIs('treasurer.cashbook*') ? 'active' : '' }}">
    <span class="nav-icon"><i class="bi bi-journal-bookmark"></i></span> Cash Book
</a>
<a href="{{ route('treasurer.expenses') }}" class="nav-link {{ request()->routeIs('treasurer.expenses*') ? 'active' : '' }}">
    <span class="nav-icon"><i class="bi bi-receipt-cutoff"></i></span> Manage Expenses
</a>
<a href="{{ route('treasurer.reports') }}" class="nav-link {{ request()->routeIs('treasurer.reports') ? 'active' : '' }}">
    <span class="nav-icon"><i class="bi bi-bar-chart-line"></i></span> Release Reports
</a>
<a href="{{ route('treasurer.semester_reports') }}" class="nav-link {{ request()->routeIs('treasurer.semester_reports*') ? 'active' : '' }}">
    <span class="nav-icon"><i class="bi bi-journals"></i></span> Semester Reports
</a>
<a href="{{ route('treasurer.announcements') }}" class="nav-link {{ request()->routeIs('treasurer.announcements') ? 'active' : '' }}">
    <span class="nav-icon"><i class="bi bi-megaphone"></i></span> Announcements
</a>
