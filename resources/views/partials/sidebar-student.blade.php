{{-- Student Sidebar Navigation --}}
<div class="nav-section-label">Explore</div>
<a href="{{ route('student.proposals') }}" class="nav-link {{ request()->routeIs('student.proposals', 'student.proposal.show') ? 'active' : '' }}">
    <span class="nav-icon"><i class="bi bi-lightbulb"></i></span> Projects
</a>
<a href="{{ route('student.announcements') }}" class="nav-link {{ request()->routeIs('student.announcements') ? 'active' : '' }}">
    <span class="nav-icon"><i class="bi bi-megaphone"></i></span> Announcements
</a>
<a href="{{ route('student.officers') }}" class="nav-link {{ request()->routeIs('student.officers') ? 'active' : '' }}">
    <span class="nav-icon"><i class="bi bi-people"></i></span> SSC Officers
</a>

<a href="{{ route('student.enrollment.index') }}" class="nav-link {{ request()->routeIs('student.enrollment*') ? 'active' : '' }}">
    <span class="nav-icon"><i class="bi bi-cash-stack"></i></span> Enrollment Fee
</a>

<div class="nav-section-label">Participate</div>
<a href="{{ route('student.feedback') }}" class="nav-link {{ request()->routeIs('student.feedback') ? 'active' : '' }}">
    <span class="nav-icon"><i class="bi bi-chat-dots"></i></span> Feedback
</a>

<div class="nav-section-label">Elections</div>
<a href="{{ route('student.candidacy') }}" class="nav-link {{ request()->routeIs('student.candidacy') ? 'active' : '' }}">
    <span class="nav-icon"><i class="bi bi-clipboard-check"></i></span> Run as Officer
</a>
<a href="{{ route('student.voting') }}" class="nav-link {{ request()->routeIs('student.voting') ? 'active' : '' }}">
    <span class="nav-icon"><i class="bi bi-box-arrow-in-up"></i></span> Vote Candidates
</a>
<a href="{{ route('student.election.results') }}" class="nav-link {{ request()->routeIs('student.election.results') ? 'active' : '' }}">
    <span class="nav-icon"><i class="bi bi-bar-chart"></i></span> Election Results
</a>
