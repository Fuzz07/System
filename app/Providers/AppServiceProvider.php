<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Proposal;
use App\Models\Expense;
use App\Models\Feedback;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('partials.sidebar-admin', function ($view) {
            $view->with([
                'pendingProposals' => Proposal::where('status', 'Pending')->count(),
                'pendingExpenses'  => Expense::where('status', 'Pending')->count(),
                'pendingFeedback'  => Feedback::where('status', 'Pending')->count(),
                'pendingStudents'  => User::where('role', 'student')->where('status', 'inactive')->count(),
            ]);
        });
    }
}
