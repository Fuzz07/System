<?php

define('LARAVEL_START', microtime(true));

// Composer autoloader
require __DIR__ . '/../vendor/autoload.php';

// ── Vercel: point Laravel's storage to /tmp which is writable ──
if (isset($_SERVER['VERCEL']) || isset($_ENV['VERCEL'])) {
    $_ENV['STORAGE_PATH'] = '/tmp/storage';
    putenv('STORAGE_PATH=/tmp/storage');
}

// Bootstrap the application
$app = require_once __DIR__ . '/../bootstrap/app.php';

// ── Vercel: override storage path on the app instance ──
if (isset($_SERVER['VERCEL']) || isset($_ENV['VERCEL'])) {
    $app->useStoragePath('/tmp/storage');
}

// Run the HTTP kernel
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
)->send();

$kernel->terminate($request, $response);
