<?php
// ── Catch ALL errors including PHP fatal errors ──
ini_set('display_errors', 0);
error_reporting(E_ALL);
ob_start();

// Shutdown handler catches PHP fatal errors that try/catch can't
register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
        ob_end_clean();
        if (! headers_sent()) {
            header('Content-Type: text/plain; charset=utf-8');
            http_response_code(500);
        }
        echo "=== PHP FATAL ERROR (Shutdown) ===\n";
        echo "Type   : " . $err['type'] . "\n";
        echo "Message: " . $err['message'] . "\n";
        echo "File   : " . $err['file'] . ":" . $err['line'] . "\n";
    } else {
        ob_end_flush();
    }
});

try {
    // Forward to Laravel's public/index.php entry point
    require __DIR__ . '/../public/index.php';
} catch (\Throwable $e) {
    ob_end_clean();
    if (! headers_sent()) {
        header('Content-Type: text/plain; charset=utf-8');
        http_response_code(500);
    }
    echo "=== THROWABLE CAUGHT IN api/index.php ===\n";
    echo get_class($e) . ": " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
    if ($prev = $e->getPrevious()) {
        echo "\n--- Previous Exception ---\n";
        echo get_class($prev) . ": " . $prev->getMessage() . "\n";
        echo "File: " . $prev->getFile() . ":" . $prev->getLine() . "\n";
        echo $prev->getTraceAsString() . "\n";
    }
}
