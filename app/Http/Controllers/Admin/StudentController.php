<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\SscHelper;
use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\User;
use App\Services\StudentPromotionService;
use App\Services\StudentRoster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $department = $request->input('department', '');
        $yearLevel = $request->input('year_level', '');
        $batch = $request->input('batch', '');
        $statusFilter = $request->input('status_filter', '');
        $showArchived = $request->input('view') === 'archived';

        // Graduates are archived and inactive; counting them here would read
        // as a pile of accounts waiting for approval.
        $totalStudents = User::where('role', 'student')->notArchived()->count();
        $activeStudents = User::where('role', 'student')->notArchived()->where('status', 'active')->count();
        $pendingStudents = User::where('role', 'student')->notArchived()->where('status', 'inactive')->count();
        $archivedStudents = User::where('role', 'student')->archived()->count();

        $query = User::where('role', 'student')
            ->when($showArchived, fn ($q) => $q->archived(), fn ($q) => $q->notArchived());

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('fullname', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('student_id', 'like', "%$search%");
            });
        }

        if (!$showArchived && $statusFilter && in_array($statusFilter, ['active', 'inactive'])) {
            $query->where('status', $statusFilter);
        }

        if ($department) {
            $query->where('department', $department);
        }

        if (!$showArchived && $yearLevel) {
            $query->where('year_level', $yearLevel);
        }

        if ($showArchived && $batch) {
            $query->where('graduated_school_year', $batch);
        }

        $users = $showArchived
            ? $query->orderByDesc('archived_at')->orderBy('fullname')->paginate(8)
            : $query->orderByDesc('created_at')->paginate(8);

        $departments = User::where('role', 'student')->select('department')->distinct()->pluck('department');
        $years = User::where('role', 'student')->notArchived()->select('year_level')->distinct()->pluck('year_level');
        $batches = User::where('role', 'student')->archived()->whereNotNull('graduated_school_year')
            ->select('graduated_school_year')->distinct()->orderByDesc('graduated_school_year')->pluck('graduated_school_year');

        return view('admin.students', compact(
            'users', 'search', 'statusFilter', 'department', 'yearLevel', 'batch', 'showArchived',
            'totalStudents', 'activeStudents', 'pendingStudents', 'archivedStudents', 'departments', 'years', 'batches'
        ));
    }

    /**
     * Adds one student from the school roster. Without an email they cannot
     * sign in yet; the student claims the record by registering with the same
     * ID number.
     */
    public function store(Request $request)
    {
        $request->merge([
            'first_name' => trim((string) $request->input('first_name')),
            'middle_name' => ($middleName = trim((string) $request->input('middle_name'))) !== '' ? $middleName : null,
            'last_name' => trim((string) $request->input('last_name')),
            'student_id' => trim((string) $request->input('student_id')),
            'email' => ($email = Str::lower(trim((string) $request->input('email')))) !== '' ? $email : null,
        ]);

        $data = $request->validate([
            'year_level' => ['required', Rule::in(StudentPromotionService::YEAR_LEVELS)],
            'department' => ['required', Rule::in(Budget::DEPARTMENTS)],
            'first_name' => ['required', 'string', 'min:2', 'max:100', 'regex:' . StudentRoster::NAME_PATTERN],
            'middle_name' => ['nullable', 'string', 'max:100', 'regex:' . StudentRoster::NAME_PATTERN],
            'last_name' => ['required', 'string', 'min:2', 'max:100', 'regex:' . StudentRoster::NAME_PATTERN],
            'student_id' => ['required', 'regex:' . StudentRoster::ID_PATTERN, 'unique:users,student_id'],
            'email' => ['nullable', 'email:rfc', 'max:255', 'ends_with:@gmail.com', 'unique:users,email'],
        ], [
            'year_level.required' => 'Choose the student\'s school year.',
            'first_name.regex' => 'First name may contain letters, spaces, periods, apostrophes, and hyphens only.',
            'middle_name.regex' => 'Middle name may contain letters, spaces, periods, apostrophes, and hyphens only.',
            'last_name.regex' => 'Last name may contain letters, spaces, periods, apostrophes, and hyphens only.',
            'student_id.regex' => 'ID number must use the format YYYY-XXXX (for example, 2024-0001).',
            'student_id.unique' => 'A student with this ID number already exists.',
            'email.ends_with' => 'Use the student\'s @gmail.com email.',
            'email.unique' => 'A student with this email already exists.',
        ]);

        $student = StudentRoster::add($data);
        SscHelper::logActivity(Auth::id(), 'STUDENT_ADD', "Added student {$student->fullname} ({$student->student_id})");

        $next = $student->email
            ? 'They can sign in after setting a password with Forgot Password.'
            : "They can activate the account by registering with ID number {$student->student_id}.";

        return redirect()->route('admin.students.index')->with('success', "{$student->fullname} was added. {$next}");
    }

    public function import(Request $request, StudentRoster $roster)
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ], [
            'csv_file.required' => 'Please choose a CSV file to import.',
            'csv_file.mimes' => 'The file must be a CSV (.csv or .txt).',
            'csv_file.max' => 'The CSV file may not be larger than 2 MB.',
        ]);

        try {
            $result = $roster->import($request->file('csv_file')->getRealPath());
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('admin.students.index')->with('danger', 'The CSV file could not be read. Nothing was imported.');
        }

        SscHelper::logActivity(Auth::id(), 'STUDENT_IMPORT', "CSV import: {$result['added']} added, {$result['existing']} already on file, {$result['invalid']} invalid");

        $message = "{$result['added']} student(s) imported.";
        if ($result['existing']) {
            $message .= " {$result['existing']} were already on file and skipped.";
        }
        if ($result['invalid']) {
            $message .= " {$result['invalid']} row(s) had problems and were not imported.";
        }

        return redirect()->route('admin.students.index')
            ->with($result['added'] || ! $result['invalid'] ? 'success' : 'danger', $message)
            ->with('import_errors', $result['errors']);
    }

    /** A CSV with the required columns in order and one example row. */
    public function template()
    {
        $rows = [StudentRoster::COLUMNS, ['1st Year', 'BSIT', 'Juan', 'Santos', 'Dela Cruz', '2026-0001']];

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, 'student_import_template.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * Brings an archived student back onto the roster as a 4th year, for anyone
     * archived at the school-year change who has not actually graduated.
     */
    public function restore(User $user)
    {
        if ($user->role !== 'student' || !$user->isArchived()) {
            return redirect()->route('admin.students.index', ['view' => 'archived'])->with('danger', 'Only archived students can be restored.');
        }

        $user->update([
            'archived_at' => null,
            'graduated_school_year' => null,
            'year_level' => '4th Year',
            'status' => 'active',
        ]);
        SscHelper::logActivity(Auth::id(), 'STUDENT_RESTORE', "Restored archived student to 4th Year: {$user->email}");

        return redirect()->route('admin.students.index', ['view' => 'archived'])->with('success', "{$user->fullname} was restored as an active 4th-year student.");
    }

    public function approve(User $user)
    {
        if ($user->role !== 'student') {
            return redirect()->route('admin.students.index')->with('danger', 'Unauthorized action.');
        }
        if ($user->isArchived()) {
            return redirect()->route('admin.students.index', ['view' => 'archived'])->with('danger', 'This student is archived. Restore them first.');
        }

        $user->update(['status' => 'active']);
        SscHelper::logActivity(Auth::id(), 'STUDENT_APPROVE', "Approved student account: {$user->email}");

        return redirect()->route('admin.students.index')->with('success', 'Student approved successfully.');
    }

    public function toggleStatus(User $user)
    {
        if ($user->role !== 'student') {
            return redirect()->route('admin.students.index')->with('danger', 'Unauthorized action.');
        }
        if ($user->isArchived()) {
            return redirect()->route('admin.students.index', ['view' => 'archived'])->with('danger', 'This student is archived. Restore them first.');
        }

        $newStatus = $user->status === 'active' ? 'inactive' : 'active';
        $user->update(['status' => $newStatus]);

        $action = $newStatus === 'active' ? 'activated' : 'deactivated';
        SscHelper::logActivity(Auth::id(), 'STUDENT_TOGGLE', "Toggled status of {$user->email} to {$newStatus}");

        return redirect()->route('admin.students.index')->with('success', "Student account successfully {$action}.");
    }

    public function destroy(User $user)
    {
        if ($user->role !== 'student') {
            return redirect()->route('admin.students.index')->with('danger', 'Unauthorized action.');
        }

        SscHelper::logActivity(Auth::id(), 'STUDENT_DELETE', "Deleted student account: {$user->email}");
        $user->delete();

        return redirect()->route('admin.students.index')->with('success', 'Student account deleted.');
    }
}
