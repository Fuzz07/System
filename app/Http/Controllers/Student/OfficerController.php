<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\User;

class OfficerController extends Controller
{
    public function index()
    {
        $officers = User::activeOfficers()->get();
        return view('student.officers', compact('officers'));
    }
}
