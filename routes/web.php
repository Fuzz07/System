<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Officer;
use App\Http\Controllers\Student;
use Illuminate\Support\Facades\Route;


// ─── Public / Landing ───
Route::get('/', function () {
    return view('welcome');
})->name('home');

// ─── Auth Routes ───
// New portal-specific login routes (e.g. /login/auth/admin)
Route::get('/login/auth/{portal}', [AuthController::class, 'showLogin'])->name('login.portal');
Route::post('/login/auth/{portal}', [AuthController::class, 'login'])->name('login.submit.portal');

// Explicit student login routes at /login/student
Route::get('/login/student', [AuthController::class, 'showLogin'])->name('login.student');
Route::post('/login/student', [AuthController::class, 'login'])->name('login.student.submit');

// Backwards-compatible redirect from old /login paths to new /login/auth/*
Route::get('/login/{portal?}', function ($portal = null) {
    if ($portal) {
        // If the portal is 'student', redirect to the explicit /login/student path
        if ($portal === 'student') {
            return redirect('/login/student');
        }
        return redirect('/login/auth/' . $portal);
    }
    // Default to student login
    return redirect('/login/student');
})->name('login');

// Keep the legacy submit route for compatibility
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::get('/register', fn() => view('auth.register'))->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/captcha/verify', [App\Http\Controllers\CaptchaController::class, 'verifyCaptcha'])->name('captcha.verify');
Route::get('/proposals/{proposal}/print', [Student\ProposalController::class, 'print'])->name('proposals.print')->middleware(['auth', 'role:admin,officer,treasurer']);

// ─── Admin Routes ───
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/budgets', [Admin\BudgetController::class, 'index'])->name('budgets');
    Route::post('/budgets', [Admin\BudgetController::class, 'store'])->name('budgets.store');
    Route::patch('/budgets/{budget}/approve', [Admin\BudgetController::class, 'approve'])->name('budgets.approve');
    Route::patch('/budgets/{budget}/reject', [Admin\BudgetController::class, 'reject'])->name('budgets.reject');
    Route::delete('/budgets/{budget}', [Admin\BudgetController::class, 'destroy'])->name('budgets.destroy');

    Route::get('/proposals', [Admin\ProposalController::class, 'index'])->name('proposals');
    Route::post('/proposals/{proposal}/review', [Admin\ProposalController::class, 'review'])->name('proposals.review');

    Route::get('/expenses', [Admin\ExpenseController::class, 'index'])->name('expenses');
    Route::post('/expenses/{expense}/review', [Admin\ExpenseController::class, 'review'])->name('expenses.review');

    Route::get('/officers', [Admin\OfficerController::class, 'index'])->name('officers');
    Route::post('/officers', [Admin\OfficerController::class, 'store'])->name('officers.store');
    Route::patch('/officers/{user}/toggle', [Admin\OfficerController::class, 'toggleStatus'])->name('officers.toggle');
    Route::patch('/officers/{user}/role', [Admin\OfficerController::class, 'changeRole'])->name('officers.role');
    Route::delete('/officers/{user}', [Admin\OfficerController::class, 'destroy'])->name('officers.destroy');

    Route::get('/students', [Admin\StudentController::class, 'index'])->name('students.index');
    Route::get('/enrollment-payments', [App\Http\Controllers\Admin\EnrollmentPaymentController::class, 'index'])->name('enrollment.payments');
    Route::post('/enrollment-payments/{payment}/mark-paid', [App\Http\Controllers\Admin\EnrollmentPaymentController::class, 'markPaid'])->name('enrollment.payments.mark_paid');
    Route::post('/enrollment-payments/{student}/walk-in', [App\Http\Controllers\Admin\EnrollmentPaymentController::class, 'markPaidWalkIn'])->name('enrollment.payments.walk_in');
    Route::post('/enrollment-payments/{payment}/approve-proof', [App\Http\Controllers\Admin\EnrollmentPaymentController::class, 'approveProof'])->name('enrollment.payments.proof.approve');
    Route::post('/enrollment-payments/{payment}/reject-proof', [App\Http\Controllers\Admin\EnrollmentPaymentController::class, 'rejectProof'])->name('enrollment.payments.proof.reject');
    Route::patch('/students/{user}/approve', [Admin\StudentController::class, 'approve'])->name('students.approve');
    Route::patch('/students/{user}/toggle', [Admin\StudentController::class, 'toggleStatus'])->name('students.toggle');
    Route::delete('/students/{user}', [Admin\StudentController::class, 'destroy'])->name('students.destroy');

    Route::get('/feedback', [Admin\FeedbackController::class, 'index'])->name('feedback');
    Route::post('/feedback/{feedback}/reply', [Admin\FeedbackController::class, 'reply'])->name('feedback.reply');

    Route::get('/logs', [Admin\LogController::class, 'index'])->name('logs');

    Route::get('/settings', [Admin\SettingsController::class, 'index'])->name('settings');
    Route::post('/settings/school-year', [Admin\SettingsController::class, 'addSchoolYear'])->name('settings.sy.add');
    Route::patch('/settings/school-year/{schoolYear}/activate', [Admin\SettingsController::class, 'activateSchoolYear'])->name('settings.sy.activate');
    Route::delete('/settings/school-year/{schoolYear}', [Admin\SettingsController::class, 'deleteSchoolYear'])->name('settings.sy.delete');
    Route::get('/settings/export', [Admin\SettingsController::class, 'export'])->name('settings.export');
    Route::post('/settings/candidacy/toggle', [Admin\SettingsController::class, 'toggleCandidacy'])->name('settings.candidacy.toggle');
    Route::get('/candidacies', [Admin\CandidacyController::class, 'index'])->name('candidacies');
    Route::delete('/candidacies/{candidacy}', [Admin\CandidacyController::class, 'destroy'])->name('candidacy.destroy');
    Route::get('/election-results', [Admin\CandidacyController::class, 'results'])->name('election.results');
});

// ─── Officer Routes ───
Route::prefix('officer')->name('officer.')->middleware(['auth', 'role:officer,treasurer'])->group(function () {
    Route::get('/dashboard', [Officer\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/proposals', [Officer\ProposalController::class, 'index'])->name('proposals');
    Route::post('/proposals', [Officer\ProposalController::class, 'store'])->name('proposals.store');
    Route::put('/proposals/{proposal}', [Officer\ProposalController::class, 'update'])->name('proposals.update');
    Route::post('/proposals/{proposal}/complete', [Officer\ProposalController::class, 'complete'])->name('proposals.complete');

    Route::get('/expenses', [Officer\ExpenseController::class, 'index'])->name('expenses');
    Route::post('/expenses', [Officer\ExpenseController::class, 'store'])->name('expenses.store');

    Route::get('/announcements', [Officer\AnnouncementController::class, 'index'])->name('announcements');
    Route::post('/announcements', [Officer\AnnouncementController::class, 'store'])->name('announcements.store');
    Route::delete('/announcements/{announcement}', [Officer\AnnouncementController::class, 'destroy'])->name('announcements.destroy');

    Route::get('/liquidation', [Officer\LiquidationController::class, 'index'])->name('liquidation');
    Route::post('/liquidation', [Officer\LiquidationController::class, 'store'])->name('liquidation.store');
});

// ─── Dean Routes ───
Route::prefix('dean')->name('dean.')->middleware(['auth', 'role:dean'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Dean\DashboardController::class, 'index'])->name('dashboard');
    Route::post('/candidacies/{candidacy}/vote', [App\Http\Controllers\Dean\DashboardController::class, 'vote'])->name('candidacy.vote');
    Route::post('/candidacies/{candidacy}/reject', [App\Http\Controllers\Dean\DashboardController::class, 'reject'])->name('candidacy.reject');
    Route::get('/election-results', [App\Http\Controllers\Dean\DashboardController::class, 'results'])->name('election.results');
});

// ─── Student Routes ───
Route::prefix('student')->name('student.')->middleware(['auth', 'role:student'])->group(function () {
    Route::get('/', [Student\DashboardController::class, 'index'])->name('overview');
    Route::post('/chatbot/chat', [Student\ChatbotController::class, 'chat'])->name('chatbot.chat');
    
    Route::get('/proposals', [Student\ProposalController::class, 'index'])->name('proposals');
    Route::get('/proposals/{proposal}', [Student\ProposalController::class, 'show'])->name('proposal.show');
    Route::post('/proposals/{proposal}/comment', [Student\ProposalController::class, 'comment'])->name('proposal.comment');

    Route::get('/announcements', [Student\AnnouncementController::class, 'index'])->name('announcements');

    Route::get('/api/announcements', function () {
        $announcements = \App\Models\Announcement::with(['author', 'proposal'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($a) {
                return [
                    'id' => $a->id,
                    'title' => $a->title,
                    'content' => $a->content,
                    'author' => $a->author->fullname ?? 'SSC Admin',
                    'date' => $a->created_at?->format('M d, Y'),
                    'time_ago' => $a->created_at?->diffForHumans(),
                    'proof' => ($a->project_id && $a->proposal?->completion_proof) ? asset('storage/' . $a->proposal->completion_proof) : null
                ];
            });
        return response()->json(['announcements' => $announcements]);
    })->name('api.announcements');

    Route::get('/feedback', [Student\FeedbackController::class, 'index'])->name('feedback');
    Route::post('/feedback', [Student\FeedbackController::class, 'store'])->name('feedback.store');

    Route::get('/officers', [Student\OfficerController::class, 'index'])->name('officers');
    Route::get('/candidacy', function () {
        return view('student.candidacy');
    })->name('candidacy');
    Route::post('/candidacy', function (\Illuminate\Http\Request $request) {
        $student = \Illuminate\Support\Facades\Auth::user();
        $department = $student->department;

        if (blank($department)) {
            return back()->with('danger', 'Your account has no assigned department. Please contact the administrator.');
        }

        $allowedPositions = [
            $department . ' Representative',
            'SSC President',
            'SSC Vice President',
            'SSC Secretary',
            'SSC Treasurer',
        ];

        $request->validate([
            'position' => ['required', 'string', 'max:100', \Illuminate\Validation\Rule::in($allowedPositions)],
            'platform' => 'required|string|min:20|max:3000',
        ]);

        $activeSy = \App\Models\SchoolYear::where('is_active', 1)->first();
        if (!$activeSy || !$activeSy->candidacy_open) {
            return back()->with('danger', 'Candidacy filing is currently closed.');
        }
        $exists = $student->candidacies()->where('school_year', $activeSy->label)->exists();
        if ($exists) {
            return back()->with('danger', 'You have already submitted an application for this school year.');
        }
        \App\Models\Candidacy::create([
            'user_id' => $student->id,
            'department' => $department,
            'position' => $request->position,
            'platform' => $request->platform,
            'status' => 'pending',
            'school_year' => $activeSy->label,
        ]);
        \App\Helpers\SscHelper::logActivity($student->id, 'CANDIDACY_APPLY', "Submitted candidacy application for {$request->position}");
        return redirect()->route('student.candidacy')->with('success', 'Your candidacy application has been submitted successfully.');
    })->name('candidacy.store');

    Route::get('/voting', [Student\VotingController::class, 'index'])->name('voting');
    Route::post('/voting', [Student\VotingController::class, 'store'])->name('voting.store');
    Route::get('/election-results', [Student\VotingController::class, 'results'])->name('election.results');
    
    // Notifications API for student app (JSON)
    Route::get('/notifications', [Student\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [Student\NotificationController::class, 'unreadCount'])->name('notifications.unread');
    
    // Device Token API for FCM push notifications
    Route::post('/api/device-token', [App\Http\Controllers\DeviceTokenController::class, 'store'])->name('device-token.store');
    Route::delete('/api/device-token', [App\Http\Controllers\DeviceTokenController::class, 'destroy'])->name('device-token.destroy');
    Route::get('/api/device-token', [App\Http\Controllers\DeviceTokenController::class, 'index'])->name('device-token.index');
    
    // Enrollment fee (student)
    Route::get('/enrollment', [Student\EnrollmentController::class, 'index'])->name('enrollment.index');
    Route::post('/enrollment', [Student\EnrollmentController::class, 'store'])->name('enrollment.store');
});

// ─── Mobile Student Routes (PWA) ───
Route::prefix('m/student')->name('mobile.student.')->middleware(['auth', 'role:student'])->group(function () {
    Route::get('/proposals', function () {
        $proposals = \App\Models\Proposal::with('officer')
            ->withCount('comments')
            ->whereIn('status', ['Approved', 'Pending'])
            ->orderByRaw("FIELD(status, 'Pending', 'Approved')")
            ->orderByDesc('created_at')
            ->get();
        return view('mobile.student.proposals', compact('proposals'));
    })->name('proposals');

    Route::get('/proposals/{proposal}', function (\App\Models\Proposal $proposal) {
        if (!in_array($proposal->status, ['Approved', 'Pending'], true)) {
            abort(404);
        }
        $proposal->load('officer');
        $comments = \App\Models\ProposalComment::with('user')
            ->where('proposal_id', $proposal->id)
            ->orderByDesc('created_at')
            ->get();
        return view('mobile.student.proposal_details', compact('proposal', 'comments'));
    })->name('proposal.show');

    Route::post('/proposals/{proposal}/comment', function (\Illuminate\Http\Request $request, \App\Models\Proposal $proposal) {
        if (!in_array($proposal->status, ['Approved', 'Pending'], true)) {
            abort(404);
        }

        $request->validate(['comment' => 'required|string|min:1|max:2000']);
        \App\Models\ProposalComment::create([
            'proposal_id' => $proposal->id,
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
            'comment' => $request->comment,
        ]);
        return redirect()->route('mobile.student.proposal.show', $proposal)->with('success', 'Comment posted!');
    })->name('proposal.comment');

    Route::get('/announcements', function () {
        $announcements = \App\Models\Announcement::with(['author', 'proposal'])
            ->orderByDesc('created_at')
            ->get();
        return view('mobile.student.announcements', compact('announcements'));
    })->name('announcements');

    Route::get('/feedback', function () {
        $feedbacks = \App\Models\Feedback::with('replier')
            ->where('student_id', \Illuminate\Support\Facades\Auth::id())
            ->orderByDesc('created_at')
            ->get();
        return view('mobile.student.feedback', compact('feedbacks'));
    })->name('feedback');

    Route::post('/feedback', function (\Illuminate\Http\Request $request) {
        $request->validate(['message' => 'required|string|min:5|max:2000']);
        \App\Models\Feedback::create([
            'student_id' => \Illuminate\Support\Facades\Auth::id(),
            'message' => $request->message,
        ]);
        return redirect()->route('mobile.student.feedback')->with('success', 'Message sent!');
    })->name('feedback.store');

    Route::get('/enrollment', function () {
        $student = \Illuminate\Support\Facades\Auth::user();
        $currentSy = config('ssc.current_school_year');
        $payment = \App\Models\EnrollmentPayment::where('user_id', $student->id)
            ->where('semester', $currentSy)
            ->orderByDesc('created_at')
            ->first();
        $amount = config('ssc.enrollment_fee_amount', 50);
        return view('mobile.student.enrollment', compact('payment', 'amount'));
    })->name('enrollment');

    Route::post('/enrollment', function (\Illuminate\Http\Request $request) {
        $student = \Illuminate\Support\Facades\Auth::user();
        $currentSy = config('ssc.current_school_year');
        $amount = config('ssc.enrollment_fee_amount', 50);

        $payment = \App\Models\EnrollmentPayment::where('user_id', $student->id)
            ->where('semester', $currentSy)
            ->orderByDesc('created_at')
            ->first();

        if ($request->hasFile('proof')) {
            $request->validate(['proof' => 'required|file|mimes:jpg,jpeg,png,pdf,mp4|max:5120']);
            if (! $payment || $payment->status === 'paid') {
                $payment = \App\Models\EnrollmentPayment::create([
                    'user_id' => $student->id,
                    'amount' => $amount,
                    'semester' => $currentSy,
                    'method' => 'gcash',
                    'status' => 'pending',
                    'reference' => 'GCASH-' . strtoupper(uniqid()),
                    'proof_status' => 'pending',
                ]);
            }
            $proofPath = $request->file('proof')->store('enrollment_proofs', 'public');
            $payment->update([
                'proof_path' => $proofPath,
                'proof_status' => 'pending',
                'proof_notes' => null,
            ]);
            return redirect()->route('mobile.student.enrollment')->with('success', 'Payment proof uploaded successfully. Admin will verify it soon.');
        }

        if ($payment && $payment->status === 'pending') {
            return redirect()->route('mobile.student.enrollment')->with('info', 'You already have a pending payment. Please upload proof or wait for admin verification.');
        }

        if ($payment && $payment->status === 'paid') {
            return redirect()->route('mobile.student.enrollment')->with('info', 'Your enrollment fee is already marked as paid.');
        }

        $payment = \App\Models\EnrollmentPayment::create([
            'user_id' => $student->id,
            'amount' => $amount,
            'semester' => $currentSy,
            'method' => 'gcash',
            'status' => 'pending',
            'reference' => 'GCASH-' . strtoupper(uniqid()),
            'proof_status' => 'pending',
        ]);

        return redirect()->route('mobile.student.enrollment')->with('success', 'Payment record created. Please upload proof after sending GCash payment. Reference: ' . $payment->reference);
    })->name('enrollment.store');

    Route::get('/officers', function () {
        $officers = \App\Models\User::whereIn('role', ['officer', 'treasurer'])
            ->where('status', 'active')
            ->orderBy('role')
            ->get();
        return view('mobile.student.officers', compact('officers'));
    })->name('officers');

    Route::get('/candidacy', function () {
        return view('mobile.student.candidacy');
    })->name('candidacy');

    Route::post('/candidacy', function (\Illuminate\Http\Request $request) {
        $student = \Illuminate\Support\Facades\Auth::user();
        $department = $student->department;

        if (blank($department)) {
            return redirect()->route('mobile.student.candidacy')->with('danger', 'Your account has no assigned department. Please contact the administrator.');
        }

        $allowedPositions = [
            $department . ' Representative',
            'SSC President',
            'SSC Vice President',
            'SSC Secretary',
            'SSC Treasurer',
        ];

        $request->validate([
            'position' => ['required', 'string', 'max:100', \Illuminate\Validation\Rule::in($allowedPositions)],
            'platform' => 'required|string|min:20|max:3000',
        ]);

        $activeSy = \App\Models\SchoolYear::where('is_active', 1)->first();
        if (!$activeSy || !$activeSy->candidacy_open) {
            return redirect()->route('mobile.student.candidacy')->with('danger', 'Candidacy filing is currently closed.');
        }
        $exists = $student->candidacies()->where('school_year', $activeSy->label)->exists();
        if ($exists) {
            return redirect()->route('mobile.student.candidacy')->with('danger', 'You have already submitted an application.');
        }
        \App\Models\Candidacy::create([
            'user_id' => $student->id,
            'department' => $department,
            'position' => $request->position,
            'platform' => $request->platform,
            'status' => 'pending',
            'school_year' => $activeSy->label,
        ]);
        \App\Helpers\SscHelper::logActivity($student->id, 'CANDIDACY_APPLY', "Submitted mobile candidacy application for {$request->position}");
        return redirect()->route('mobile.student.candidacy')->with('success', 'Application submitted!');
    })->name('candidacy.store');

    Route::get('/voting', [Student\VotingController::class, 'indexMobile'])->name('voting');
    Route::post('/voting', [Student\VotingController::class, 'storeMobile'])->name('voting.store');
    Route::get('/election-results', [Student\VotingController::class, 'resultsMobile'])->name('election.results');
});

// ─── Treasurer Routes ───
Route::prefix('treasurer')->name('treasurer.')->middleware(['auth', 'role:treasurer'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Treasurer\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/release', [App\Http\Controllers\Treasurer\ReleaseController::class, 'index'])->name('release');
    Route::post('/release', [App\Http\Controllers\Treasurer\ReleaseController::class, 'store'])->name('release.submit');
    Route::get('/reports', [App\Http\Controllers\Treasurer\ReleaseController::class, 'reports'])->name('reports');
    Route::get('/announcements', [App\Http\Controllers\Treasurer\ReleaseController::class, 'announcements'])->name('announcements');
});
