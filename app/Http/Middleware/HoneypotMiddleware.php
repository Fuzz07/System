<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\SscHelper;

class HoneypotMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('PATCH') || $request->isMethod('DELETE')) {
            if ($request->has('website_url') && !empty($request->input('website_url'))) {
                // Log bot activity in the database log for administrators to see
                SscHelper::logActivity(
                    null,
                    'BOT_BLOCKED',
                    "Automated bot submission blocked. IP: " . $request->ip()
                );

                // Return unprocessable content error to block bot
                abort(422, 'Security check failed. Automated submission detected.');
            }
        }

        return $next($request);
    }
}
