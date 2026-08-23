@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-admin') @endsection

@section('content')
    <div class="page-header">
        <div>
            <h1>Eligible Students</h1>
            <p>Manage the MS account whitelist — only listed emails may register</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-brand d-inline-flex align-items-center gap-2 px-3 py-2"
                data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="bi bi-upload"></i> Import CSV
            </button>
            <button type="button" class="btn btn-brand d-inline-flex align-items-center gap-2 px-4 py-2 shadow-sm"
                data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="bi bi-plus-circle"></i> Add Email
            </button>
        </div>
    </div>

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100"
                style="border-radius:var(--radius);background:linear-gradient(135deg,#1e3a5f,#2563eb);">
                <div class="card-body p-4 d-flex align-items-center gap-3">
                    <div
                        style="width:48px;height:48px;border-radius:12px;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-shield-check" style="font-size:1.5rem;color:#fff;"></i>
                    </div>
                    <div>
                        <div style="font-size:1.7rem;font-weight:800;color:#fff;line-height:1;">{{ $total }}</div>
                        <div style="font-size:.8rem;color:rgba(255,255,255,.75);font-weight:500;">Total Eligible Emails
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius:var(--radius);background:#fff;">
                <div class="card-body p-4 d-flex align-items-center gap-3">
                    <div
                        style="width:48px;height:48px;border-radius:12px;background:#dcfce7;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-person-check" style="font-size:1.5rem;color:#16a34a;"></i>
                    </div>
                    <div>
                        <div style="font-size:1.7rem;font-weight:800;color:#1e293b;line-height:1;">{{ $registered }}</div>
                        <div style="font-size:.8rem;color:#64748b;font-weight:500;">Already Registered</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius:var(--radius);background:#fff;">
                <div class="card-body p-4 d-flex align-items-center gap-3">
                    <div
                        style="width:48px;height:48px;border-radius:12px;background:#fef9c3;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-hourglass-split" style="font-size:1.5rem;color:#ca8a04;"></i>
                    </div>
                    <div>
                        <div style="font-size:1.7rem;font-weight:800;color:#1e293b;line-height:1;">
                            {{ $total - $registered }}
                        </div>
                        <div style="font-size:.8rem;color:#64748b;font-weight:500;">Not Yet Registered</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Whitelist Feature Info --}}
    @if(config('ssc.enforce_eligibility_whitelist', true))
        <div class="alert border-0 mb-4 d-flex align-items-start gap-3"
            style="background:#eff6ff;border-radius:var(--radius);padding:16px 20px;">
            <i class="bi bi-shield-lock-fill" style="color:#2563eb;font-size:1.2rem;margin-top:2px;"></i>
            <div>
                <strong style="color:#1e3a5f;font-size:.88rem;">Whitelist is ACTIVE</strong>
                <div style="font-size:.82rem;color:#475569;margin-top:2px;">Only MS accounts in this list can complete student
                    registration. To disable this, set <code>ENFORCE_ELIGIBILITY_WHITELIST=false</code> in your
                    <code>.env</code>.
                </div>
            </div>
        </div>
    @else
        <div class="alert border-0 mb-4 d-flex align-items-start gap-3"
            style="background:#fef2f2;border-radius:var(--radius);padding:16px 20px;">
            <i class="bi bi-shield-exclamation" style="color:#dc2626;font-size:1.2rem;margin-top:2px;"></i>
            <div>
                <strong style="color:#991b1b;font-size:.88rem;">Whitelist is DISABLED</strong>
                <div style="font-size:.82rem;color:#475569;margin-top:2px;">Any <code>@mcclawis.edu.ph</code> account can
                    currently register. Set <code>ENFORCE_ELIGIBILITY_WHITELIST=true</code> in <code>.env</code> to enforce the
                    whitelist.</div>
            </div>
        </div>
    @endif

    {{-- Search / Filter --}}
    <div class="card mb-4">
        <div class="card-body-custom">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-5"><input type="text" name="search" class="form-control-custom"
                        placeholder="Search by email or name..." value="{{ $search }}"></div>
                <div class="col-md-3">
                    <select name="department" class="form-select-custom">
                        <option value="">All Departments</option>
                        @foreach($departments as $d)
                            <option value="{{ $d }}" {{ $department === $d ? 'selected' : '' }}>{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn-primary-custom flex-fill justify-content-center"><i
                            class="bi bi-search"></i></button>
                    @if($search || $department)<a href="{{ route('admin.eligible_students.index') }}"
                    class="btn btn-outline-secondary">Reset</a>@endif
                </div>
            </form>
        </div>
    </div>

    {{-- CSV Template Download --}}
    <div class="d-flex justify-content-end mb-2">
        <a href="data:text/csv;charset=utf-8,email%2Cstudent_name%2Cdepartment%2Cyear_level%0Ajuan.dela.cruz%40mcclawis.edu.ph%2CJuan%20Dela%20Cruz%2CBSIT%2C3rd%20Year"
            download="eligible_students_template.csv"
            class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1"
            style="font-size:.78rem;border-radius:6px;">
            <i class="bi bi-download"></i> Download CSV Template
        </a>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="card-header-custom">
            <span class="card-title">Eligible Student Accounts</span>
            <span class="badge bg-secondary">{{ $eligible->total() }} records</span>
        </div>
        <div class="table-responsive-custom">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>MS Account (Email)</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Year Level</th>
                        <th>Status</th>
                        <th>Date Added</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($eligible as $e)
                        @php $isRegistered = \App\Models\User::where('email', $e->email)->exists(); @endphp
                        <tr>
                            <td>
                                <div style="font-weight:700;font-size:.88rem;color:#1e293b;">{{ $e->email }}</div>
                            </td>
                            <td style="font-size:.85rem;">{{ $e->student_name ?? '—' }}</td>
                            <td style="font-size:.85rem;">{{ $e->department ?? '—' }}</td>
                            <td style="font-size:.82rem;">{{ $e->year_level ?? '—' }}</td>
                            <td>
                                @if($isRegistered)
                                    <span class="badge bg-success" style="font-size:.7rem;border-radius:6px;padding:4px 10px;">
                                        <i class="bi bi-check-circle-fill me-1"></i>Registered
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark"
                                        style="font-size:.7rem;border-radius:6px;padding:4px 10px;">
                                        <i class="bi bi-hourglass me-1"></i>Pending
                                    </span>
                                @endif
                            </td>
                            <td style="font-size:.78rem;white-space:nowrap;color:#64748b;">
                                {{ \Carbon\Carbon::parse($e->created_at)->format('M d, Y') }}
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.eligible_students.destroy', $e) }}" class="d-inline"
                                    onsubmit="return confirm('Remove {{ $e->email }} from the eligible list?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" style="font-size:.72rem;border-radius:6px;" {{ $isRegistered ? 'title=This student is already registered' : '' }}>
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-shield-x" style="font-size:2.5rem;opacity:.15;"></i>
                                <div class="mt-3 fw-semibold">No eligible emails added yet.</div>
                                <p class="text-muted mb-0 mt-1" style="font-size:.85rem;">Use the "Add Email" or "Import CSV"
                                    buttons above to get started.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $eligible->withQueryString()->links('partials.pagination') }}
    </div>

    {{-- Add Single Email Modal --}}
    <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content"
                style="border-radius:var(--radius);border:none;box-shadow:0 10px 25px rgba(0,0,0,.12);">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title" style="font-weight:700;"><i class="bi bi-plus-circle"></i> Add Eligible Student
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1);"></button>
                </div>
                <form method="POST" action="{{ route('admin.eligible_students.store') }}">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label-custom">MS Account Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control-custom"
                                placeholder="juan.delacruz@mcclawis.edu.ph" required>
                            <small class="text-muted" style="font-size:.78rem;">Must end with @mcclawis.edu.ph</small>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label-custom">Student Name</label>
                                <input type="text" name="student_name" class="form-control-custom"
                                    placeholder="Juan Dela Cruz">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">Department</label>
                                <input type="text" name="department" class="form-control-custom" placeholder="e.g. BSIT">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom">Year Level</label>
                            <select name="year_level" class="form-select-custom">
                                <option value="">— Select —</option>
                                @foreach(['1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year'] as $y)
                                    <option>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom">Notes <span class="text-muted"
                                    style="font-weight:400;font-size:.78rem;">(optional)</span></label>
                            <textarea name="notes" class="form-control-custom" rows="2"
                                placeholder="Any notes about this student..." style="resize:vertical;"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-primary-custom"><i class="bi bi-check2"></i> Add to List</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Import CSV Modal --}}
    <div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content"
                style="border-radius:var(--radius);border:none;box-shadow:0 10px 25px rgba(0,0,0,.12);">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title" style="font-weight:700;"><i class="bi bi-upload"></i> Import from CSV</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1);"></button>
                </div>
                <form method="POST" action="{{ route('admin.eligible_students.import') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3 p-3"
                            style="background:#f8fafc;border-radius:var(--radius-sm);border:1px dashed #cbd5e1;">
                            <div style="font-size:.82rem;font-weight:700;color:#1e293b;margin-bottom:8px;"><i
                                    class="bi bi-info-circle text-primary me-1"></i>Expected CSV Format</div>
                            <code style="font-size:.78rem;color:#475569;display:block;white-space:pre-line;">email,student_name,department,year_level
        juan.dela.cruz@mcclawis.edu.ph,Juan Dela Cruz,BSIT,3rd Year
        maria.santos@mcclawis.edu.ph,Maria Santos,BSED,2nd Year</code>
                            <div class="mt-2" style="font-size:.75rem;color:#64748b;">
                                • Header row is automatically detected and skipped<br>
                                • Only <strong>@mcclawis.edu.ph</strong> emails are accepted<br>
                                • Duplicate emails are silently skipped
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom">Select CSV File <span class="text-danger">*</span></label>
                            <input type="file" name="csv_file" id="csv_file" accept=".csv,.txt" class="form-control-custom"
                                required>
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