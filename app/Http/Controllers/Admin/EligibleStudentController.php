<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\SscHelper;
use App\Http\Controllers\Controller;
use App\Models\EligibleStudent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EligibleStudentController extends Controller
{
    public function index(Request $request)
    {
        $search     = $request->input('search', '');
        $department = $request->input('department', '');

        $query = EligibleStudent::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%$search%")
                  ->orWhere('student_name', 'like', "%$search%");
            });
        }

        if ($department) {
            $query->where('department', $department);
        }

        $eligible = $query->orderByDesc('created_at')->paginate(8);

        // Stats
        $total      = EligibleStudent::count();
        $registered = EligibleStudent::whereIn(
            'email',
            User::where('role', 'student')->pluck('email')
        )->count();

        $departments = EligibleStudent::select('department')
            ->whereNotNull('department')
            ->distinct()
            ->pluck('department');

        return view('admin.eligible_students', compact(
            'eligible', 'search', 'department', 'departments',
            'total', 'registered'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'email'        => 'required|email|ends_with:@mcclawis.edu.ph|unique:eligible_students,email',
            'student_name' => 'nullable|string|max:200',
            'department'   => 'nullable|string|max:100',
            'year_level'   => 'nullable|string|max:50',
            'notes'        => 'nullable|string|max:500',
        ], [
            'email.unique'      => 'This email is already in the eligible students list.',
            'email.ends_with'   => 'Only @mcclawis.edu.ph emails are accepted.',
        ]);

        EligibleStudent::create([
            'email'        => strtolower(trim($request->email)),
            'student_name' => $request->student_name,
            'department'   => $request->department,
            'year_level'   => $request->year_level,
            'notes'        => $request->notes,
            'imported_by'  => Auth::id(),
        ]);

        SscHelper::logActivity(Auth::id(), 'ELIGIBLE_STUDENT_ADD', "Added eligible student: {$request->email}");
        return redirect()->route('admin.eligible_students.index')->with('success', 'Email added to eligible list successfully.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ], [
            'csv_file.required' => 'Please select a CSV file to import.',
            'csv_file.mimes'    => 'File must be a CSV (.csv or .txt) file.',
        ]);

        $file    = $request->file('csv_file');
        $handle  = fopen($file->getRealPath(), 'r');

        $imported  = 0;
        $skipped   = 0;
        $invalid   = 0;
        $firstRow  = true;

        while (($row = fgetcsv($handle)) !== false) {
            // Skip header row
            if ($firstRow) {
                $firstRow = false;
                // If it looks like a header (first cell is 'email' literally), skip
                if (isset($row[0]) && strtolower(trim($row[0])) === 'email') {
                    continue;
                }
            }

            $email = strtolower(trim($row[0] ?? ''));
            $name  = trim($row[1] ?? '');
            $dept  = trim($row[2] ?? '');
            $year  = trim($row[3] ?? '');

            if (empty($email)) continue;

            // Validate domain
            if (!str_ends_with($email, '@mcclawis.edu.ph')) {
                $invalid++;
                continue;
            }

            // Skip duplicates silently
            if (EligibleStudent::where('email', $email)->exists()) {
                $skipped++;
                continue;
            }

            EligibleStudent::create([
                'email'        => $email,
                'student_name' => $name ?: null,
                'department'   => $dept ?: null,
                'year_level'   => $year ?: null,
                'imported_by'  => Auth::id(),
            ]);

            $imported++;
        }

        fclose($handle);

        SscHelper::logActivity(Auth::id(), 'ELIGIBLE_STUDENT_IMPORT', "CSV Import: {$imported} added, {$skipped} duplicates skipped, {$invalid} invalid domain.");

        $message = "{$imported} email(s) imported successfully.";
        if ($skipped > 0) $message .= " {$skipped} duplicate(s) skipped.";
        if ($invalid > 0) $message .= " {$invalid} row(s) had invalid or non-school email domains and were ignored.";

        return redirect()->route('admin.eligible_students.index')->with('success', $message);
    }

    public function destroy(EligibleStudent $eligible)
    {
        SscHelper::logActivity(Auth::id(), 'ELIGIBLE_STUDENT_REMOVE', "Removed eligible student: {$eligible->email}");
        $eligible->delete();
        return redirect()->route('admin.eligible_students.index')->with('success', 'Email removed from eligible list.');
    }
}
