<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// ── Detect serverless / read-only filesystem environment ──
// On Vercel, files live under /var/task/ and storage is read-only.
// We detect this by checking the file path — no env var needed.
$isVercel = str_contains(__FILE__, '/var/task/')
         || str_contains(__FILE__, DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'task' . DIRECTORY_SEPARATOR);

// Fallback: also check env vars in case Vercel exposes them (requires
// "Expose System Environment Variables" to be ON in Project Settings)
if (! $isVercel) {
    $isVercel = isset($_SERVER['VERCEL'])
             || isset($_ENV['VERCEL'])
             || (getenv('VERCEL') !== false);
}

// ── Writable-storage setup ──
// If we're on Vercel (or any read-only filesystem), create writable
// directories under /tmp BEFORE Application::configure() is called,
// so that ViewServiceProvider picks up the correct compiled-views path.
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
}

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeadersMiddleware::class,
            \App\Http\Middleware\HoneypotMiddleware::class,
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // ── Safe fallback renderer ──
        // Prevents the infinite recursion caused by the exception handler
        // itself trying to render a Blade view when Blade/storage fails.
        // Returns plain text so there is ALWAYS a response body on errors.
        // On non-Vercel environments this only activates when storage is unwritable.
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            // Only override if storage is not writable (Vercel, etc.)
            // or if the error is the view-binding circular failure itself.
            $storageOk = is_writable(storage_path('framework/views'));
            $isBindingErr = $e instanceof \Illuminate\Contracts\Container\BindingResolutionException
                         && str_contains($e->getMessage(), '[view]');

            if ($storageOk && ! $isBindingErr) {
                return null; // Let Laravel's default handler work normally
            }

            $status = ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface)
                ? $e->getStatusCode()
                : 500;

            if (config('app.debug')) {
                return response(
                    implode("\n\n", [
                        '=== ERROR (Vercel Debug Mode) ===',
                        get_class($e) . ': ' . $e->getMessage(),
                        'File: ' . $e->getFile() . ':' . $e->getLine(),
                        'Storage writable: ' . ($storageOk ? 'YES' : 'NO'),
                        'Storage path: ' . storage_path(),
                        'Trace:',
                        $e->getTraceAsString(),
                    ]),
                    $status,
                    ['Content-Type' => 'text/plain; charset=utf-8']
                );
            }

            return response('HTTP ' . $status . ' – Server Error. Please try again.', $status);
        });
    })->create();

// Apply storage path AFTER create() as well (belt-and-suspenders)
if ($isVercel) {
    $app->useStoragePath('/tmp/storage');
}

return $app;
