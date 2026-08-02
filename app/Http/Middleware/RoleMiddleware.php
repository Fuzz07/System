<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        if (!$request->user() || !in_array($request->user()->role, $roles)) {
            abort(403, 'Unauthorized access.');
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
