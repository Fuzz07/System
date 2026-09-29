@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-admin') @endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h4 class="mb-0">Student Accounts</h4>
            <small class="text-muted">Total: {{ $totalStudents }} • Active: {{ $activeStudents }} • Pending: {{ $pendingStudents }} • Archived: {{ $archivedStudents }}</small>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-brand d-inline-flex align-items-center gap-2 px-3 py-2" data-bs-toggle="modal" data-bs-target="#importStudentsModal">
                <i class="bi bi-upload"></i> Import CSV
            </button>
            <button type="button" class="btn btn-brand d-inline-flex align-items-center gap-2 px-4 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                <i class="bi bi-person-plus"></i> Add Student
            </button>
        </div>
    </div>

    @if(session('import_errors'))
    <div class="alert alert-warning small mb-3">
        <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle me-1"></i> These rows were not imported. Fix them in the file and import it again; rows already added will be skipped.</div>
        <ul class="mb-0 ps-3">
            @foreach(session('import_errors') as $importError)
            <li>{{ $importError }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <ul class="nav nav-pills nav-brand d-inline-flex gap-1 bg-white border rounded-3 p-1 mb-3 shadow-sm">
        <li class="nav-item">
            <a href="{{ route('admin.students.index') }}" class="nav-link d-flex align-items-center gap-2 {{ !$showArchived ? 'active' : '' }}"><i class="bi bi-people"></i> Current Students</a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.students.index', ['view' => 'archived']) }}" class="nav-link d-flex align-items-center gap-2 {{ $showArchived ? 'active' : '' }}"><i class="bi bi-archive"></i> Archived / Graduated ({{ $archivedStudents }})</a>
        </li>
    </ul>

    @if($showArchived)
    <div class="alert alert-light border small mb-3">
        <i class="bi bi-info-circle me-1"></i> 4th-year students are archived automatically when a later school year is set active in Settings. Restore anyone who has not actually graduated.
    </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="p-3">
                <form class="row g-2 mb-3" method="GET">
                    @if($showArchived)<input type="hidden" name="view" value="archived">@endif
                    <div class="col-md-4">
                        <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control" placeholder="Search by name, email, or student id">
                    </div>
                    <div class="col-md-3">
                        <select name="department" class="form-select">
                            <option value="">All Departments</option>
                            @foreach($departments as $d)
                                <option value="{{ $d }}" {{ (isset($department) && $department == $d) ? 'selected' : '' }}>{{ $d }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        @if($showArchived)
                        <select name="batch" class="form-select">
                            <option value="">All Batches</option>
                            @foreach($batches as $b)
                                <option value="{{ $b }}" {{ $batch == $b ? 'selected' : '' }}>SY {{ $b }}</option>
                            @endforeach
                        </select>
                        @else
                        <select name="year_level" class="form-select">
                            <option value="">All Years</option>
                            @foreach($years as $y)
                                <option value="{{ $y }}" {{ (isset($yearLevel) && $yearLevel == $y) ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                        @endif
                    </div>
                    <div class="col-md-3 text-end">
                        <button class="btn btn-primary">Filter</button>
                    </div>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Student ID</th>
                            <th>Department</th>
                            @if($showArchived)
                            <th>Graduated</th>
                            <th>Archived On</th>
                            <th>Status</th>
                            @else
                            <th>Year</th>
                            <th>Status</th>
                            @endif
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td>{{ $user->fullname }}</td>
                            <td>
                                @if($user->email)
                                {{ $user->email }}
                                @else
                                <span class="text-muted fst-italic" title="Added by the admin. The student activates the account by registering with this ID number.">Not registered yet</span>
                                @endif
                            </td>
                            <td>{{ $user->student_id }}</td>
                            <td>{{ $user->department }}</td>
                            @if($showArchived)
                            <td>{{ $user->graduated_school_year ? 'SY ' . $user->graduated_school_year : $user->year_level }}</td>
                            <td>{{ $user->archived_at?->format('M d, Y') }}</td>
                            <td>
                                {{-- Grey, not the yellow used for sign-ups awaiting approval. --}}
                                <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($user->status) }}</span>
                            </td>
                            @else
                            <td>{{ $user->year_level }}</td>
                            <td>
                                <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'warning' }}">{{ ucfirst($user->status) }}</span>
                            </td>
                            @endif
                            <td class="text-end">
                                @if($showArchived)
                                <form action="{{ route('admin.students.restore', $user) }}" method="POST" class="d-inline" data-confirm="Restore {{ $user->fullname }} as an active 4th-year student?">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm btn-outline-primary">Restore</button>
                                </form>
                                @elseif($user->status === 'inactive')
                                <form action="{{ route('admin.students.approve', $user) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm btn-primary">Approve</button>
                                </form>
                                @else
                                <form action="{{ route('admin.students.toggle', $user) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm btn-warning">Deactivate</button>
                                </form>
                                @endif

                                <form action="{{ route('admin.students.destroy', $user) }}" method="POST" class="d-inline" data-confirm="Delete this account?">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $showArchived ? 8 : 7 }}" class="text-center text-muted py-4">{{ $showArchived ? 'No archived students yet.' : 'No students found.' }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $users->withQueryString()->links('partials.pagination') }}
        </div>
    </div>
</div>

{{-- Add Student Modal: same fields, same order as the CSV import. --}}
<div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:var(--radius);border:none;box-shadow:0 10px 25px rgba(0,0,0,.12);">
            <div class="modal-header modal-header-custom">
                <h5 class="modal-title" id="addStudentTitle" style="font-weight:700;"><i class="bi bi-person-plus"></i> Add Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter:invert(1);"></button>
            </div>
            <form method="POST" action="{{ route('admin.students.store') }}">
                @csrf
                <input type="hidden" name="_form" value="add_student">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="add_year_level" class="form-label-custom">School Year <span class="text-danger">*</span></label>
                            <select id="add_year_level" name="year_level" class="form-select-custom" required>
                                <option value="">— Select —</option>
                                @foreach(\App\Services\StudentPromotionService::YEAR_LEVELS as $level)
                                <option value="{{ $level }}" @selected(old('year_level') === $level)>{{ $level }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="add_department" class="form-label-custom">Department <span class="text-danger">*</span></label>
                            <select id="add_department" name="department" class="form-select-custom" required>
                                <option value="">— Select —</option>
                                @foreach(\App\Models\Budget::DEPARTMENTS as $dept)
                                <option value="{{ $dept }}" @selected(old('department') === $dept)>{{ $dept }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="add_first_name" class="form-label-custom">First Name <span class="text-danger">*</span></label>
                            <input id="add_first_name" name="first_name" class="form-control-custom" value="{{ old('first_name') }}" minlength="2" maxlength="100" required>
                        </div>
                        <div class="col-md-4">
                            <label for="add_middle_name" class="form-label-custom">Middle Name <span class="text-muted fw-normal">(optional)</span></label>
                            <input id="add_middle_name" name="middle_name" class="form-control-custom" value="{{ old('middle_name') }}" maxlength="100">
                        </div>
                        <div class="col-md-4">
                            <label for="add_last_name" class="form-label-custom">Last Name <span class="text-danger">*</span></label>
                            <input id="add_last_name" name="last_name" class="form-control-custom" value="{{ old('last_name') }}" minlength="2" maxlength="100" required>
                        </div>
                        <div class="col-md-6">
                            <label for="add_student_id" class="form-label-custom">ID Number <span class="text-danger">*</span></label>
                            <input id="add_student_id" name="student_id" class="form-control-custom" value="{{ old('student_id') }}" pattern="\d{4}-\d{4}" maxlength="9" placeholder="YYYY-XXXX" required>
                        </div>
                        <div class="col-md-6">
                            <label for="add_email" class="form-label-custom">Gmail <span class="text-muted fw-normal">(optional)</span></label>
                            <input id="add_email" type="email" name="email" class="form-control-custom" value="{{ old('email') }}" maxlength="255" placeholder="student@gmail.com">
                        </div>
                    </div>
                    <div class="small text-muted mt-3">
                        <i class="bi bi-info-circle me-1"></i> Without a Gmail, the student activates this account by registering with the same ID number and last name.
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-primary-custom"><i class="bi bi-check2"></i> Add Student</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Import CSV Modal --}}
<div class="modal fade" id="importStudentsModal" tabindex="-1" aria-labelledby="importStudentsTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:var(--radius);border:none;box-shadow:0 10px 25px rgba(0,0,0,.12);">
            <div class="modal-header modal-header-custom">
                <h5 class="modal-title" id="importStudentsTitle" style="font-weight:700;"><i class="bi bi-upload"></i> Import Students from CSV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter:invert(1);"></button>
            </div>
            <form method="POST" action="{{ route('admin.students.import') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_form" value="import_students">
                <div class="modal-body p-4">
                    <div class="fw-bold small mb-2">The columns must be in this order:</div>
                    <div class="table-responsive mb-3">
                        <table class="table table-sm table-bordered align-middle mb-0 small">
                            <thead class="table-light">
                                <tr><th>Column</th><th>Heading</th><th>Required</th><th>Accepted values</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>A</td><td>School Year</td><td>Yes</td><td>1st Year, 2nd Year, 3rd Year, 4th Year (also 1, 2nd, "First Year")</td></tr>
                                <tr><td>B</td><td>Department</td><td>Yes</td><td>{{ implode(', ', \App\Models\Budget::DEPARTMENTS) }}</td></tr>
                                <tr><td>C</td><td>First Name</td><td>Yes</td><td>Letters, spaces, . ' -</td></tr>
                                <tr><td>D</td><td>Middle Name</td><td>No</td><td>Leave the cell blank if none</td></tr>
                                <tr><td>E</td><td>Last Name</td><td>Yes</td><td>Letters, spaces, . ' -</td></tr>
                                <tr><td>F</td><td>ID Number</td><td>Yes</td><td>YYYY-XXXX, e.g. 2026-0001</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <ul class="small text-muted ps-3 mb-3">
                        <li>The heading row is optional and is detected automatically.</li>
                        <li>Students whose ID number is already on file are skipped, so the same file can be imported again safely.</li>
                        <li>Rows with problems are listed after the import; the rest are still added.</li>
                        <li>Imported students activate their account by registering with their ID number.</li>
                    </ul>
                    <a href="{{ route('admin.students.template') }}" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-download"></i> Download CSV template</a>
                    <div>
                        <label for="csv_file" class="form-label-custom">CSV file <span class="text-danger">*</span></label>
                        <input type="file" id="csv_file" name="csv_file" accept=".csv,.txt" class="form-control-custom" required>
                        <div class="form-text">In Excel: File → Save As → "CSV UTF-8 (Comma delimited)". Up to 2 MB.</div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-primary-custom"><i class="bi bi-upload"></i> Import Now</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if($errors->any() && in_array(old('_form'), ['add_student', 'import_students'], true))
<script>
    // Reopen the form that failed validation so the admin can fix it in place.
    document.addEventListener('DOMContentLoaded', function () {
        var modal = document.getElementById(@json(old('_form') === 'add_student' ? 'addStudentModal' : 'importStudentsModal'));
        if (modal && window.bootstrap) bootstrap.Modal.getOrCreateInstance(modal).show();
    });
</script>
@endif
@endpush
