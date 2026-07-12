<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\User;

class OfficerController extends Controller
{
    public function index()
    {
        $officers = User::whereIn('role', ['treasurer', 'officer'])
            ->where('status', 'active')
            ->orderByRaw("FIELD(role, 'officer', 'treasurer')")
            ->orderBy('fullname')
            ->get();
        return view('student.officers', compact('officers'));
    }
}
