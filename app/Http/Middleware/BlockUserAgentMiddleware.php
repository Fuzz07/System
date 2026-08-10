<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\SscHelper;

class BlockUserAgentMiddleware
{
    /**
     * List of blocked user agent keywords / patterns (case-insensitive).
     *
     * @var array<string>
     */
    protected array $blockedUserAgents = [
        'curl',
        'culr',
        'xcurl',
        'wget',
        'python-requests',
        'sqlmap',
        'acunetix',
        'nikto',
        'libwww-perl',
        'go-http-client',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userAgent = strtolower($request->header('User-Agent', ''));

        foreach ($this->blockedUserAgents as $botPattern) {
            if (str_contains($userAgent, $botPattern)) {
                // Log blocked activity for auditing
                if (class_exists(SscHelper::class)) {
                    SscHelper::logActivity(
                        null,
                        'BOT_BLOCKED',
                        "Automated bot/tool request blocked [User-Agent: {$request->header('User-Agent')}]. IP: " . $request->ip()
                    );
                }

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Access denied: Automated request tools are not allowed.'
                    ], 403);
                }

                return response('Access Denied: Automated request tools (curl, xcurl, etc.) are blocked.', 403);
            }
        }

        return $next($request);
    }
}
