<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\AnnouncementApiController;
use App\Http\Controllers\Api\BudgetApiController;
use App\Http\Controllers\Api\OfficerApiController;
use App\Http\Controllers\Api\StudentApiController;
use App\Http\Controllers\Api\VotingApiController;

/*
|--------------------------------------------------------------------------
| API Routes (v1)
|--------------------------------------------------------------------------
|
| Stateless REST API authenticated via JWT Bearer tokens.
|
*/

Route::prefix('v1')->group(function () {

    // ── Public Auth Endpoints ──────────────────────────────────────────
    Route::post('/auth/login', [AuthApiController::class, 'login'])->name('api.auth.login');

    // ── Authenticated Endpoints (Requires valid JWT) ───────────────────
    Route::middleware(['jwt'])->group(function () {

        // Auth session
        Route::post('/auth/logout', [AuthApiController::class, 'logout'])->name('api.auth.logout');
        Route::post('/auth/refresh', [AuthApiController::class, 'refresh'])->name('api.auth.refresh');
        Route::get('/auth/me', [AuthApiController::class, 'me'])->name('api.auth.me');

        // Announcements
        Route::get('/announcements', [AnnouncementApiController::class, 'index'])->name('api.announcements.index');
        Route::get('/announcements/{id}', [AnnouncementApiController::class, 'show'])->name('api.announcements.show');
        Route::post('/announcements/{id}/comments', [AnnouncementApiController::class, 'comment'])->name('api.announcements.comment');

        // Budgets & Expenses Transparency
        Route::get('/budgets', [BudgetApiController::class, 'index'])->name('api.budgets.index');
        Route::get('/expenses', [BudgetApiController::class, 'expenses'])->name('api.expenses.index');

        // Officers Roster
        Route::get('/officers', [OfficerApiController::class, 'index'])->name('api.officers.index');

        // ── Student Portal Endpoints ───────────────────────────────────
        Route::middleware(['role:student'])->group(function () {
            // Profile & Enrollment
            Route::get('/student/profile', [StudentApiController::class, 'profile'])->name('api.student.profile');
            Route::get('/student/enrollment', [StudentApiController::class, 'enrollment'])->name('api.student.enrollment');
            Route::post('/student/enrollment/proof', [StudentApiController::class, 'uploadEnrollmentProof'])->name('api.student.enrollment.proof');

            // Voting
            Route::get('/voting/status', [VotingApiController::class, 'status'])->name('api.voting.status');
            Route::get('/voting/candidates', [VotingApiController::class, 'candidates'])->name('api.voting.candidates');
            Route::post('/voting/start-ballot', [VotingApiController::class, 'startBallot'])->name('api.voting.start_ballot');
            Route::post('/voting/vote', [VotingApiController::class, 'castVote'])->name('api.voting.vote');
            Route::get('/voting/my-votes', [VotingApiController::class, 'myVotes'])->name('api.voting.my_votes');
        });
    });
});
