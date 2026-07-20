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
        if (config('app.env') === 'production' || isset($_SERVER['VERCEL']) || isset($_ENV['VERCEL']) || getenv('VERCEL') !== false) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Setup Firebase key from base64 environment variable if needed
        $this->setupFirebaseKey();

        View::composer('partials.sidebar-admin', function ($view) {
            $view->with([
                'pendingProposals' => Proposal::where('status', 'Pending')->count(),
                'pendingExpenses'  => Expense::where('status', 'Pending')->count(),
                'pendingFeedback'  => Feedback::where('status', 'Pending')->count(),
                'pendingStudents'  => User::where('role', 'student')->where('status', 'inactive')->count(),
            ]);
        });
    }

    /**
     * Setup Firebase key file from base64 environment variable.
     * This allows deploying the key via environment variables on Vercel/Railway.
     */
    private function setupFirebaseKey(): void
    {
        try {
            $keyB64 = env('FIREBASE_SERVICE_ACCOUNT_KEY_B64');
            if (!$keyB64) {
                return; // No base64 key provided, will use file path
            }

            $storagePath = storage_path('firebase-key.json');
            
            // If key file already exists, use it
            if (file_exists($storagePath)) {
                return;
            }

            // Decode base64 and create key file
            $keyContent = base64_decode($keyB64, true);
            if (!$keyContent) {
                \Log::error('Failed to decode Firebase service account key from environment variable');
                return;
            }

            // Ensure storage directory exists
            $storageDir = dirname($storagePath);
            if (!is_dir($storageDir)) {
                mkdir($storageDir, 0755, true);
            }

            // Write key file with restricted permissions
            file_put_contents($storagePath, $keyContent);
            chmod($storagePath, 0600);

            \Log::info('Firebase service account key successfully created from environment variable');
        } catch (\Exception $e) {
            \Log::error('Error setting up Firebase key', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
