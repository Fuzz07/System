<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
try {
    /** @var Application $app */
    $app = require_once __DIR__.'/../bootstrap/app.php';

    $app->handleRequest(Request::capture());
} catch (\Throwable $e) {
    error_log("=== CRITICAL EXCEPTION CAUGHT IN INDEX.PHP ===");
    error_log(get_class($e) . ": " . $e->getMessage());
    error_log($e->getTraceAsString());
    if ($e->getPrevious()) {
        error_log("--- PREVIOUS EXCEPTION ---");
        error_log(get_class($e->getPrevious()) . ": " . $e->getPrevious()->getMessage());
        error_log($e->getPrevious()->getTraceAsString());
    }
    throw $e;
}

