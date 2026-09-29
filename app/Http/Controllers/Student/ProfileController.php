<?php

namespace App\Http\Controllers\Student;

use App\Helpers\SscHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('student.profile', ['student' => Auth::user()]);
    }

    public function mobileEdit()
    {
        return view('mobile.student.profile', ['student' => Auth::user()]);
    }

    public function update(Request $request)
    {
        /** @var User $student */
        $student = Auth::user();

        $request->merge([
            'first_name' => trim((string) $request->input('first_name')),
            'middle_name' => ($middleName = trim((string) $request->input('middle_name'))) !== '' ? $middleName : null,
            'last_name' => trim((string) $request->input('last_name')),
            'student_id' => trim((string) $request->input('student_id')),
            'email' => Str::lower(trim((string) $request->input('email'))),
        ]);

        $emailChanged = $request->input('email') !== Str::lower((string) $student->email);
        $passwordChanged = filled($request->input('password'));

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL][\pL\s.\'-]*$/u'],
            'middle_name' => ['nullable', 'string', 'max:100', 'regex:/^[\pL][\pL\s.\'-]*$/u'],
            'last_name' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL][\pL\s.\'-]*$/u'],
            'birthdate' => ['required', 'date', 'after_or_equal:' . User::earliestBirthdate(), 'before_or_equal:' . User::latestBirthdate()],
            'year_level' => ['required', Rule::in(['1st Year', '2nd Year', '3rd Year', '4th Year'])],
            'department' => ['required', Rule::in(['BEED', 'BSED', 'BSBA', 'BSHM', 'BSIT'])],
            'student_id' => ['required', 'regex:/^\d{4}-\d{4}$/', Rule::unique('users', 'student_id')->ignore($student->id)],
            'email' => ['required', 'email:rfc', 'max:255', 'ends_with:@gmail.com', Rule::unique('users', 'email')->ignore($student->id)],
            'current_password' => [Rule::requiredIf($emailChanged || $passwordChanged), 'nullable', 'current_password:web'],
            'password' => ['nullable', 'min:8', 'confirmed', 'regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/'],
        ], [
            'first_name.regex' => 'First name may contain letters, spaces, periods, apostrophes, and hyphens only.',
            'middle_name.regex' => 'Middle name may contain letters, spaces, periods, apostrophes, and hyphens only.',
            'last_name.regex' => 'Last name may contain letters, spaces, periods, apostrophes, and hyphens only.',
            'birthdate.required' => 'Please enter your date of birth.',
            'birthdate.before_or_equal' => 'You must be at least ' . User::MIN_AGE . ' years old.',
            'birthdate.after_or_equal' => 'Please enter a valid date of birth.',
            'student_id.regex' => 'Student ID must use the format YYYY-XXXX (for example, 2024-0001).',
            'student_id.unique' => 'This Student ID is already associated with another account.',
            'email.ends_with' => 'Please use a valid @gmail.com account.',
            'email.unique' => 'This Gmail account is already associated with another account.',
            'current_password.required' => 'Enter your current password to change your email or password.',
            'current_password.current_password' => 'The current password is incorrect.',
            'password.regex' => 'The new password must contain at least one letter and one number.',
        ]);

        $student->fill([
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'fullname' => trim(implode(' ', array_filter([
                $validated['first_name'],
                $validated['middle_name'] ?? null,
                $validated['last_name'],
            ]))),
            // The age shown in the form is display-only; work it out here.
            'birthdate' => $validated['birthdate'],
            'age' => \Illuminate\Support\Carbon::parse($validated['birthdate'])->age,
            'year_level' => $validated['year_level'],
            'department' => $validated['department'],
            'student_id' => $validated['student_id'],
            'email' => $validated['email'],
        ]);

        if ($passwordChanged) {
            $student->password = $validated['password'];
        }

        $student->save();

        SscHelper::logActivity($student->id, 'PROFILE_UPDATE', 'Student updated their account details');

        return redirect()->route(
            $request->routeIs('mobile.*') ? 'mobile.student.profile.edit' : 'student.profile.edit'
        )->with('success', 'Your account details have been updated.');
    }
}
