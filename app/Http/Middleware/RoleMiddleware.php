<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        $user = $request->user() ?? auth('api')->user();

        if (!$user || !in_array($user->role, $roles)) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden. You do not have the required permissions for this action.',
                ], 403);
            }
            abort(403, 'Unauthorized access.');
        }

        // ── Archived (graduated) students ───────
        // Graduates are archived when a new school year starts. Close any
        // session they still have open so the portal stops working for them
        // right away instead of whenever they next log in.
        $sessionUser = $request->user();
        if ($sessionUser && $sessionUser->role === 'student' && $sessionUser->isArchived()) {
            \Illuminate\Support\Facades\Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login.student')->withErrors([
                'email' => 'Your account has been archived because you have graduated. Please contact the administrator if this is incorrect.',
            ]);
        }

        // ── Admin Active Session / Device Check ───────
        $user = $request->user();
        if ($user && $user->role === 'admin') {
            if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'admin_device_token')) {
                $registeredToken = $user->admin_device_token;
                if (!empty($registeredToken)) {
                    $cookieToken = $request->cookie('admin_device_token');
                    if ($cookieToken !== $registeredToken) {
                        \Illuminate\Support\Facades\Auth::logout();
                        $request->session()->invalidate();
                        $request->session()->regenerateToken();

                        return redirect()->route('login', ['portal' => 'admin'])->withErrors([
                            'email' => 'Your session has been terminated because this device is no longer authorized.',
                        ]);
                    }
                }
            }
        }

        return $next($request);
    }
}
