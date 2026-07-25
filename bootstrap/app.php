<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// ── Detect Vercel serverless environment ──
// Files live under /var/task/ on Vercel — reliable path-based detection,
// no need for VERCEL env var (which requires "Expose System Env Vars" to be ON).
$isVercel = str_contains(__FILE__, '/var/task/');

// Also accept env var detection as a fallback (if Expose System Env Vars IS enabled)
if (! $isVercel) {
    $isVercel = (getenv('VERCEL') !== false)
             || isset($_SERVER['VERCEL'])
             || isset($_ENV['VERCEL']);
}

// ── Create writable /tmp/storage dirs BEFORE Application::configure() ──
// Must happen before configure() so ViewServiceProvider picks up the correct
// compiled-views path when it registers its bindings.
if ($isVercel) {
    $tmpStorage = '/tmp/storage';
    foreach ([
        "$tmpStorage/framework/sessions",
        "$tmpStorage/framework/views",
        "$tmpStorage/framework/cache/data",
        "$tmpStorage/logs",
        "$tmpStorage/app/public",
    ] as $dir) {
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }

    // Direct Laravel cache and compiled paths to /tmp to bypass read-only filesystem.
    // This resolves errors like "bootstrap/cache directory must be present and writable".
    $cacheOverrides = [
        'APP_CONFIG_CACHE'   => '/tmp/config.php',
        'APP_EVENTS_CACHE'   => '/tmp/events.php',
        'APP_PACKAGES_CACHE' => '/tmp/packages.php',
        'APP_ROUTES_CACHE'   => '/tmp/routes.php',
        'APP_SERVICES_CACHE' => '/tmp/services.php',
        'VIEW_COMPILED_PATH' => '/tmp/storage/framework/views',
    ];

    foreach ($cacheOverrides as $key => $val) {
        putenv("$key=$val");
        $_ENV[$key] = $val;
        $_SERVER[$key] = $val;
    }

    // Set storage path env variable that Application constructor reads.
    putenv("LARAVEL_STORAGE_PATH=$tmpStorage");
    $_ENV['LARAVEL_STORAGE_PATH'] = $tmpStorage;
}

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust all proxies (critical for accurate client IP address resolution under Vercel)
        $middleware->trustProxies(at: '*');

        // Exclude AJAX registration and enrollment submissions from CSRF validation to prevent 419 mismatches
        $middleware->validateCsrfTokens(except: [
            'register/check-email',
            'register/verify-otp',
            'student/chatbot/chat',
            'm/student/enrollment',
            'student/enrollment',
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeadersMiddleware::class,
            \App\Http\Middleware\HoneypotMiddleware::class,
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) use ($isVercel): void {
        // ── Critical: Vercel-safe exception renderer ──
        //
        // On Vercel, the default Laravel error handler calls response() helper
        // which needs ResponseFactory → ViewFactory → 'view' binding.
        // If 'view' isn't bound yet (circular bootstrap issue), this causes an
        // infinite loop ending in a fatal crash with no response body.
        //
        // Fix: on Vercel, ALWAYS return an Illuminate\Http\Response directly
        // (bypassing the service container entirely) so a response is always sent.
        //
        // On non-Vercel (local dev), return null to let Laravel use its
        // beautiful default error pages normally.
        if ($isVercel) {
            $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
                $status = ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface)
                    ? $e->getStatusCode()
                    : 500;

                // 1. Return JSON for AJAX, API, or Fetch requests to support graceful Toast/Modal errors
                if ($request->expectsJson() || $request->ajax()) {
                    return new \Illuminate\Http\Response(
                        json_encode([
                            'success' => false,
                            'message' => config('app.debug') ? $e->getMessage() : 'An error occurred. Please try again.'
                        ]),
                        $status,
                        ['Content-Type' => 'application/json; charset=utf-8']
                    );
                }

                // 2. Return gorgeous, premium HTML pages instead of raw plain-text
                if (config('app.debug')) {
                    $body = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vercel Laravel Error (Debug Mode)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #3730a3;
            --slate-50: #f8fafc;
            --slate-100: #f1f5f9;
            --slate-300: #cbd5e1;
            --slate-700: #334155;
            --slate-800: #1e293b;
            --slate-900: #0f172a;
            --red-500: #ef4444;
            --red-50: #fef2f2;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: radial-gradient(circle at top right, #fff5f5, #f8fafc);
            color: var(--slate-800);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .error-card {
            background: #ffffff;
            border-radius: 32px;
            width: 100%;
            max-width: 640px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 20px 50px rgba(239, 68, 68, 0.05), 0 4px 12px rgba(0, 0, 0, 0.01);
            border: 1px solid rgba(239, 68, 68, 0.1);
            animation: floatIn 0.5s ease-out;
        }
        @keyframes floatIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .icon-circle {
            width: 80px;
            height: 80px;
            background: var(--red-50);
            color: var(--red-500);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 24px auto;
            border: 2px dashed rgba(239, 68, 68, 0.2);
        }
        .error-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--slate-900);
            margin-bottom: 12px;
            letter-spacing: -0.5px;
        }
        .error-message {
            font-size: 0.95rem;
            color: var(--slate-700);
            line-height: 1.6;
            margin-bottom: 24px;
        }
        .error-code {
            display: inline-block;
            background: var(--slate-100);
            color: var(--slate-700);
            font-family: monospace;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            margin-bottom: 24px;
            border: 1px solid var(--slate-300);
        }
        .console-panel {
            text-align: left;
            background: #1e293b;
            color: #38bdf8;
            font-family: "Fira Code", Monaco, Consolas, monospace;
            font-size: 0.78rem;
            padding: 20px;
            border-radius: 16px;
            overflow-x: auto;
            max-height: 250px;
            margin-bottom: 24px;
            box-shadow: inset 0 2px 8px rgba(0,0,0,0.2);
            line-height: 1.5;
            white-space: pre-wrap;
        }
        .btn-go-back {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #ffffff;
            border: none;
            border-radius: 16px;
            padding: 14px 28px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35);
            transition: all 0.2s;
            text-decoration: none;
        }
        .btn-go-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.45);
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="icon-circle">
            <i class="bi bi-bug-fill"></i>
        </div>
        <h1 class="error-title">Vercel Exception Debug</h1>
        <p class="error-message">An unhandled server-side exception was thrown during execution.</p>
        <span class="error-code">Status: \' . $status . \' &bull; \' . get_class($e) . \'</span>
        
        <div class="console-panel">
            <strong>Exception Details:</strong><br>
            Message: \' . e($e->getMessage()) . \'<br>
            File: \' . e($e->getFile()) . \':\' . $e->getLine() . \'<br><br>
            <strong>Stack Trace:</strong><br>
            \' . e($e->getTraceAsString()) . \'
        </div>

        <a href="javascript:history.back()" class="btn-go-back">
            <i class="bi bi-arrow-left"></i> Go Back Safely
        </a>
    </div>
</body>
</html>\';
                } else {
                    $body = \'<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oops! An Error Occurred</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #3730a3;
            --slate-100: #f1f5f9;
            --slate-300: #cbd5e1;
            --slate-700: #334155;
            --slate-800: #1e293b;
            --slate-900: #0f172a;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: radial-gradient(circle at top right, #f5f3ff, #f8fafc);
            color: var(--slate-800);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .error-card {
            background: #ffffff;
            border-radius: 32px;
            width: 100%;
            max-width: 480px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 20px 50px rgba(79, 70, 229, 0.05), 0 4px 12px rgba(0, 0, 0, 0.01);
            border: 1px solid rgba(79, 70, 229, 0.06);
            animation: floatIn 0.5s ease-out;
        }
        @keyframes floatIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .icon-circle {
            width: 80px;
            height: 80px;
            background: rgba(79, 70, 229, 0.06);
            color: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 24px auto;
            border: 2px dashed rgba(79, 70, 229, 0.2);
        }
        .error-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--slate-900);
            margin-bottom: 12px;
            letter-spacing: -0.5px;
        }
        .error-message {
            font-size: 0.95rem;
            color: var(--slate-700);
            line-height: 1.6;
            margin-bottom: 32px;
        }
        .error-code {
            display: inline-block;
            background: var(--slate-100);
            color: var(--slate-700);
            font-family: monospace;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            margin-bottom: 24px;
            border: 1px solid var(--slate-300);
        }
        .btn-go-back {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #ffffff;
            border: none;
            border-radius: 16px;
            padding: 14px 28px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35);
            transition: all 0.2s;
            text-decoration: none;
        }
        .btn-go-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.45);
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="icon-circle">
            <i class="bi bi-exclamation-triangle-fill"></i>
        </div>
        <h1 class="error-title">Oops! System Encountered an Issue</h1>
        <p class="error-message">Something went wrong on our end while processing your request. Please try again or go back to the previous screen.</p>
        <span class="error-code">Error Code: \' . $status . \'</span>
        <a href="javascript:history.back()" class="btn-go-back">
            <i class="bi bi-arrow-left"></i> Go Back Safely
        </a>
    </div>
</body>
</html>\';
                }

                return new \Illuminate\Http\Response(
                    $body,
                    $status,
                    ['Content-Type' => 'text/html; charset=utf-8']
                );
            });
        }
    })->create();

// Apply storage path AFTER create() as well (belt-and-suspenders).
if ($isVercel) {
    $app->useStoragePath('/tmp/storage');
}

return $app;
