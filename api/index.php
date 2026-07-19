<?php
// ── TEMPORARY DIAGNOSTIC — REMOVE AFTER DEBUGGING ──
header('Content-Type: text/plain; charset=utf-8');

echo "=== Vercel PHP Diagnostic ===\n\n";
echo "PHP Version : " . PHP_VERSION . "\n";
echo "__FILE__    : " . __FILE__ . "\n";
echo "getcwd()    : " . getcwd() . "\n\n";

// Filesystem checks
echo "--- Filesystem ---\n";
echo "/tmp writable       : " . (is_writable('/tmp') ? 'YES' : 'NO') . "\n";
echo "/var/task exists    : " . (is_dir('/var/task') ? 'YES' : 'NO') . "\n";
echo "/var/task/user exist: " . (is_dir('/var/task/user') ? 'YES' : 'NO') . "\n\n";

// Check if vendor exists
$vendor = __DIR__ . '/../vendor/autoload.php';
echo "vendor/autoload.php : " . (file_exists($vendor) ? 'EXISTS' : 'MISSING!') . "\n";

// Check bootstrap/app.php
$bootstrap = __DIR__ . '/../bootstrap/app.php';
echo "bootstrap/app.php   : " . (file_exists($bootstrap) ? 'EXISTS' : 'MISSING!') . "\n\n";

// Env vars
echo "--- Environment Variables ---\n";
$vars = ['APP_KEY','APP_ENV','APP_DEBUG','APP_NAME','VERCEL','VERCEL_ENV',
         'DB_CONNECTION','DB_HOST','DB_PORT','DB_DATABASE',
         'SESSION_DRIVER','CACHE_STORE','LOG_CHANNEL'];
foreach ($vars as $v) {
    $val = getenv($v);
    if ($val === false) {
        echo "$v = (NOT SET)\n";
    } elseif (strlen($val) > 60) {
        echo "$v = " . substr($val, 0, 60) . "...\n";
    } else {
        echo "$v = $val\n";
    }
}

echo "\n=== End Diagnostic ===\n";
