# run_enrollment_migration_prod.ps1
# Backup + run one migration on production (Railway) DB.
# Review values below BEFORE running.

$host = "hayabusa.proxy.rlwy.net"
$port = "56748"
$db   = "railway"
$user = "root"
$pass = "YgoEqsWjIfxELVYilSOAdpQHZZryumzy"

# 1) Attempt quick dump if mysqldump is available
$dumpFile = "railway_backup_$((Get-Date).ToString('yyyyMMdd_HHmmss')).sql"
if (Get-Command mysqldump -ErrorAction SilentlyContinue) {
    Write-Host "mysqldump found — creating backup to $dumpFile"
    & mysqldump --host=$host --port=$port --user=$user --password=$pass $db > $dumpFile
    if ($LASTEXITCODE -eq 0) { Write-Host "Backup completed: $dumpFile" }
    else { Write-Host "mysqldump failed (exit $LASTEXITCODE). Create a Railway snapshot manually and abort if needed." }
} else {
    Write-Host "mysqldump not found on PATH. Please create a snapshot/backup in Railway console before proceeding."
    Read-Host "Press Enter to CONTINUE or Ctrl+C to ABORT"
}

# 2) Export env vars for artisan to use
$env:DB_CONNECTION = "mysql"
$env:DB_HOST       = $host
$env:DB_PORT       = $port
$env:DB_DATABASE   = $db
$env:DB_USERNAME   = $user
$env:DB_PASSWORD   = $pass

# 3) Show pending migrations (optional)
Write-Host "=== Migration Status (before) ==="
php artisan migrate:status

# 4) Run only the enrollment migration file
$migrationPath = "database/migrations/2026_07_20_000000_create_enrollment_payments_table.php"
Write-Host "Running migration file: $migrationPath"
php artisan migrate --path="$migrationPath" --force

# 5) Show pending migrations (after)
Write-Host "=== Migration Status (after) ==="
php artisan migrate:status

# 6) Quick DB verification (requires mysql client)
if (Get-Command mysql -ErrorAction SilentlyContinue) {
    Write-Host "Verifying table exists via mysql client..."
    & mysql --host=$host --port=$port --user=$user --password=$pass -e "SHOW TABLES LIKE 'enrollment_payments';" $db
} else {
    Write-Host "mysql client not found — you can verify from Railway UI or run:"
    Write-Host "mysql --host=$host --port=$port --user=$user --password=YOUR_PASS -e \"SHOW TABLES LIKE 'enrollment_payments';\" $db"
}

Write-Host "Done. If you see errors, do NOT proceed further until you've examined them."