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

                // Use new \Illuminate\Http\Response() DIRECTLY — NOT response() helper.
                // response() needs ResponseFactory → ViewFactory → 'view' (may not be bound).
                // Direct instantiation bypasses the service container entirely.
                if (config('app.debug')) {
                    $body = implode("\n\n", [
                        '=== Vercel Laravel Error (Debug Mode) ===',
                        'Exception : ' . get_class($e),
                        'Message   : ' . $e->getMessage(),
                        'File      : ' . $e->getFile() . ':' . $e->getLine(),
                        'Storage   : ' . storage_path(),
                        'Writable  : ' . (is_writable(storage_path('framework/views')) ? 'YES' : 'NO'),
                        'Trace     :' . "\n" . $e->getTraceAsString(),
                    ]);
                } else {
                    $body = 'HTTP ' . $status . ' – An error occurred. Please try again.';
                }

                return new \Illuminate\Http\Response(
                    $body,
                    $status,
                    ['Content-Type' => 'text/plain; charset=utf-8']
                );
            });
        }
    })->create();

// Apply storage path AFTER create() as well (belt-and-suspenders).
if ($isVercel) {
    $app->useStoragePath('/tmp/storage');
}

return $app;
