<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\SscHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OfficerController extends Controller
{
    private const MANAGED_ROLES = ['officer', 'treasurer'];

    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $roleFilter = $request->input('role_filter', '');

        $query = User::whereIn('role', self::MANAGED_ROLES);
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('fullname', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
            });
        }
        if ($roleFilter && in_array($roleFilter, self::MANAGED_ROLES, true)) {
            $query->where('role', $roleFilter);
        }
        $users = $query->orderByDesc('created_at')->get();

        return view('admin.officers', compact('users', 'search', 'roleFilter'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name'  => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name'   => 'required|string|max:100',
            'age'         => 'required|integer|min:10',
            'year_level'  => 'required|string',
            'department'  => 'required|string|max:100',
            'student_id'  => 'required|string',
            'email'       => 'required|email|unique:users,email|ends_with:@mcclawis.edu.ph',
            'password'    => 'required|min:6',
            'role'        => 'required|in:officer,treasurer',
        ]);

        $fullname = trim("{$request->first_name} {$request->middle_name} {$request->last_name}");

        User::create([
            'first_name'  => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name'   => $request->last_name,
            'age'         => $request->age,
            'year_level'  => $request->year_level,
            'department'  => $request->department,
            'student_id'  => $request->student_id,
            'fullname'    => $fullname,
            'email'       => $request->email,
            'password'    => $request->password,
            'role'        => $request->role,
        ]);

        SscHelper::logActivity(Auth::id(), 'USER_ADD', "Added user: {$request->email} ({$request->role})");
        return redirect()->route('admin.officers')->with('success', 'User created successfully.');
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeManagedOfficer($user);

        $request->validate([
            'first_name'  => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name'   => 'required|string|max:100',
            'age'         => 'required|integer|min:10',
            'year_level'  => 'required|string',
            'department'  => 'required|string|max:100',
            'student_id'  => 'required|string',
            'email'       => 'required|email|unique:users,email,' . $user->id . '|ends_with:@mcclawis.edu.ph',
            'password'    => 'nullable|min:6',
            'role'        => 'required|in:officer,treasurer',
        ]);

        $fullname = trim("{$request->first_name} {$request->middle_name} {$request->last_name}");

        $updateData = [
            'first_name'  => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name'   => $request->last_name,
            'age'         => $request->age,
            'year_level'  => $request->year_level,
            'department'  => $request->department,
            'student_id'  => $request->student_id,
            'fullname'    => $fullname,
            'email'       => $request->email,
            'role'        => $request->role,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = $request->password;
        }

        $user->update($updateData);

        SscHelper::logActivity(Auth::id(), 'USER_UPDATE', "Updated user: {$request->email} ({$request->role})");
        return redirect()->route('admin.officers')->with('success', 'User updated successfully.');
    }

    public function toggleStatus(User $user)
    {
        $this->authorizeManagedOfficer($user);

        if ($user->id === Auth::id()) {
            return redirect()->route('admin.officers')->with('danger', 'Cannot modify your own account.');
        }
        $user->update(['status' => $user->status === 'active' ? 'inactive' : 'active']);
        return redirect()->route('admin.officers')->with('success', 'User status updated.');
    }

    public function changeRole(Request $request, User $user)
    {
        $this->authorizeManagedOfficer($user);

        $request->validate(['new_role' => 'required|in:officer,treasurer,student']);
        if ($user->id !== Auth::id()) {
            $user->update(['role' => $request->new_role]);
        }
        return redirect()->route('admin.officers')->with('success', 'User role updated.');
    }

    public function destroy(User $user)
    {
        $this->authorizeManagedOfficer($user);

        if ($user->id === Auth::id()) {
            return redirect()->route('admin.officers')->with('danger', 'Cannot delete your own account.');
        }
        SscHelper::logActivity(Auth::id(), 'USER_DELETE', "Deleted user ID: {$user->id}");
        $user->delete();
        return redirect()->route('admin.officers')->with('success', 'User deleted.');
    }

    private function authorizeManagedOfficer(User $user): void
    {
        if (!in_array($user->role, self::MANAGED_ROLES, true)) {
            abort(404);
        }
    }
}