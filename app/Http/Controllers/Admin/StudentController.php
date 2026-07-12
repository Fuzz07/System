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
        $statusFilter = $request->input('status_filter', '');

        $totalStudents = User::where('role', 'student')->count();
        $activeStudents = User::where('role', 'student')->where('status', 'active')->count();
        $pendingStudents = User::where('role', 'student')->where('status', 'inactive')->count();

        $query = User::where('role', 'student');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('fullname', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('student_id', 'like', "%$search%");
            });
        }

        if ($statusFilter && in_array($statusFilter, ['active', 'inactive'])) {
            $query->where('status', $statusFilter);
        }

        $users = $query->orderByDesc('created_at')->get();

        return view('admin.students', compact(
            'users', 'search', 'statusFilter', 
            'totalStudents', 'activeStudents', 'pendingStudents'
        ));
    }

    public function approve(User $user)
    {
        if ($user->role !== 'student') {
            return redirect()->route('admin.students.index')->with('danger', 'Unauthorized action.');
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
