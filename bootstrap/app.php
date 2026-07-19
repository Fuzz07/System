<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// ── Vercel Compatibility: override storage to /tmp (writable on Vercel) ──
// This MUST run before Application::configure() so that the ViewServiceProvider
// and other providers that read the storage path during registration use /tmp/storage.
$isVercel = isset($_SERVER['VERCEL']) || isset($_ENV['VERCEL']) || (getenv('VERCEL') !== false);


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

    // Override the storage path at the PHP level BEFORE the Application is created.
    // Application::useStoragePath() is called inside configure()->create() using
    // a bound callback, so we set an env variable that the Application constructor reads.
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
        // Global middleware — applied to all web requests
        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeadersMiddleware::class,
            \App\Http\Middleware\HoneypotMiddleware::class,
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // On Vercel, render ALL exceptions as plain text to avoid
        // the circular dependency: error handler needs view, view needs
        // writable storage, writable storage depends on boot order.
        if (isset($_SERVER['VERCEL']) || isset($_ENV['VERCEL'])) {
            $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
                $status = ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface)
                    ? $e->getStatusCode()
                    : 500;
                $debug = (bool) env('APP_DEBUG', false);
                if ($debug) {
                    return response(
                        implode("\n", [
                            'Error ' . $status . ': ' . get_class($e),
                            'Message: ' . $e->getMessage(),
                            'File: ' . $e->getFile() . ':' . $e->getLine(),
                            '',
                            'Trace:',
                            $e->getTraceAsString(),
                        ]),
                        $status,
                        ['Content-Type' => 'text/plain']
                    );
                }
                return response('An error occurred (' . $status . '). Please try again later.', $status);
            });
        }
    })->create();

// Apply storage path override after app creation as well (belt-and-suspenders).
if ($isVercel) {
    $app->useStoragePath('/tmp/storage');
}

return $app;
