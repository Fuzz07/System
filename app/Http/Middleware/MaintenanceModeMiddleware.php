<?php

namespace App\Http\Middleware;

use App\Helpers\MaintenanceHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceModeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If maintenance mode is NOT active, proceed normally
        if (!MaintenanceHelper::isDown()) {
            return $next($request);
        }

        // 1. Health check & static asset bypass
        if ($request->is('up') ||
            $request->is('assets/*') ||
            $request->is('build/*') ||
            $request->is('storage/*') ||
            $request->is('favicon.ico')) {
            return $next($request);
        }

        // 2. Allow logged-in administrators full access
        if (Auth::check() && Auth::user()->role === 'admin') {
            return $next($request);
        }

        // 3. Allow access to admin portal domain, routes, and admin login / OTP flows
        $host = $request->getHost();
        $isAdminDomain = str_starts_with($host, 'admin.');

        if ($isAdminDomain ||
            $request->is('admin') ||
            $request->is('admin/*') ||
            $request->is('login/admin/*') ||
            $request->is('login/auth/admin') ||
            $request->is('logout')) {
            return $next($request);
        }

        // Maintenance data for display
        $data = MaintenanceHelper::getData();
        $message = $data['message'] ?? 'The system is currently undergoing scheduled maintenance. Please check back shortly.';

        // JSON response for API or AJAX calls
        if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
            return response()->json([
                'status' => 'maintenance',
                'message' => $message,
                'maintenance' => true,
            ], 503);
        }

        // Render dedicated 503 Maintenance Mode view
        return response()->view('errors.503', [
            'message' => $message,
            'maintenanceData' => $data,
        ], 503);
    }
}
