<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\SscHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
