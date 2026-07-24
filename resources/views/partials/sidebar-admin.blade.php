{{-- Admin Sidebar Navigation --}}
<div class="nav-section-label">Main</div>
<a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
    <span class="nav-icon"><i class="bi bi-grid-1x2"></i></span> Dashboard
</a>

<div class="nav-section-label">Financial</div>
<a href="{{ route('admin.budgets') }}" class="nav-link {{ request()->routeIs('admin.budgets') ? 'active' : '' }}">
    <span class="nav-icon"><i class="bi bi-wallet2"></i></span> Budget Management
</a>
<a href="{{ route('admin.enrollment.payments') }}" class="nav-link {{ request()->routeIs('admin.enrollment.payments') ? 'active' : '' }}">
    <span class="nav-icon"><i class="bi bi-cash-stack"></i></span> Enrollment Payments
</a>
<a href="{{ route('admin.proposals') }}" class="nav-link {{ request()->routeIs('admin.proposals') ? 'active' : '' }}">
    <span class="nav-icon"><i class="bi bi-file-earmark-text"></i></span> Proposals
    @if(($pendingProposals ?? 0) > 0)<span class="badge-count">{{ $pendingProposals }}</span>@endif
</a>
<a href="{{ route('admin.expenses') }}" class="nav-link {{ request()->routeIs('admin.expenses') ? 'active' : '' }}">
    <span class="nav-icon"><i class="bi bi-receipt"></i></span> Expenses
    @if(($pendingExpenses ?? 0) > 0)<span class="badge-count">{{ $pendingExpenses }}</span>@endif
</a>

<div class="nav-section-label">Management</div>
<a href="{{ route('admin.announcements') }}" class="nav-link {{ request()->routeIs('admin.announcements') ? 'active' : '' }}">
    <span class="nav-icon"><i class="bi bi-megaphone"></i></span> Announcements
</a>
<a href="{{ route('admin.officers') }}" class="nav-link {{ request()->routeIs('admin.officers') ? 'active' : '' }}">
    <span class="nav-icon"><i class="bi bi-people"></i></span> Manage Officers
</a>
<a href="{{ route('admin.students.index') }}" class="nav-link {{ request()->routeIs('admin.students*') ? 'active' : '' }}">
    <span class="nav-icon"><i class="bi bi-person-lines-fill"></i></span> Manage Students
    @if(($pendingStudents ?? 0) > 0)<span class="badge-count">{{ $pendingStudents }}</span>@endif
</a>
<a href="{{ route('admin.candidacies') }}" class="nav-link {{ request()->routeIs('admin.candidacies') ? 'active' : '' }}">
    <span class="nav-icon"><i class="bi bi-clipboard-check"></i></span> Candidacy Filings
</a>
<a href="{{ route('admin.election.results') }}" class="nav-link {{ request()->routeIs('admin.election.results') ? 'active' : '' }}">
    <span class="nav-icon"><i class="bi bi-bar-chart"></i></span> Election Results
</a>
<a href="{{ route('admin.feedback') }}" class="nav-link {{ request()->routeIs('admin.feedback') ? 'active' : '' }}">
    <span class="nav-icon"><i class="bi bi-chat-dots"></i></span> Feedback
    @if(($pendingFeedback ?? 0) > 0)<span class="badge-count">{{ $pendingFeedback }}</span>@endif
</a>
<a href="{{ route('admin.logs') }}" class="nav-link {{ request()->routeIs('admin.logs') ? 'active' : '' }}">
    <span class="nav-icon"><i class="bi bi-clock-history"></i></span> Activity Logs
</a>
<a href="{{ route('admin.settings') }}" class="nav-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
    <span class="nav-icon"><i class="bi bi-gear"></i></span> Settings
</a>