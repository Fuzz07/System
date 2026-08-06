<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\SscHelper;
use App\Http\Controllers\Controller;
use App\Models\SchoolYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    public function index()
    {
        $schoolYears = SchoolYear::orderByDesc('id')->get();
        $dbStats = [];
        $tables = ['users','budgets','proposals','expenses','announcements','feedback','activity_logs','liquidations'];
        foreach ($tables as $t) {
            $dbStats[$t] = DB::table($t)->count();
        }

        $activeSessions = [];
        if (\Illuminate\Support\Facades\Schema::hasTable('sessions')) {
            $lifetime = config('session.lifetime', 120) * 60;
            $cutoff = now()->timestamp - $lifetime;
            $currentSessionId = session()->getId();

            $dbSessions = DB::table('sessions')
                ->where('user_id', Auth::id())
                ->where('last_activity', '>=', $cutoff)
                ->orderByDesc('last_activity')
                ->get();

            foreach ($dbSessions as $s) {
                $parsedAgent = SscHelper::parseUserAgent($s->user_agent);
                $activeSessions[] = (object) [
                    'id' => $s->id,
                    'ip_address' => $s->ip_address ?: 'Unknown IP',
                    'user_agent' => $s->user_agent,
                    'device_info' => $parsedAgent,
                    'last_activity' => \Carbon\Carbon::createFromTimestamp($s->last_activity)->diffForHumans(),
                    'last_activity_raw' => $s->last_activity,
                    'is_current' => ($s->id === $currentSessionId),
                ];
            }
        }

        return view('admin.settings', compact('schoolYears', 'dbStats', 'activeSessions'));
    }

    public function logoutDevice(Request $request, string $sessionId)
    {
        $user = Auth::user();

        if (!\Illuminate\Support\Facades\Schema::hasTable('sessions')) {
            return redirect()->route('admin.settings')->with('danger', 'Sessions table does not exist.');
        }

        $sessionRow = DB::table('sessions')
            ->where('id', $sessionId)
            ->where('user_id', $user->id)
            ->first();

        if (!$sessionRow) {
            return redirect()->route('admin.settings')->with('danger', 'Device session not found or already logged out.');
        }

        $isCurrent = ($sessionId === session()->getId());
        $deviceInfo = SscHelper::parseUserAgent($sessionRow->user_agent);

        DB::table('sessions')
            ->where('id', $sessionId)
            ->where('user_id', $user->id)
            ->delete();

        SscHelper::logActivity($user->id, 'ADMIN_DEVICE_LOGOUT', "Logged out device session: {$deviceInfo['label']} (IP: {$sessionRow->ip_address})");

        if ($isCurrent) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/login')->with('info', 'You logged out your current device session.');
        }

        return redirect()->route('admin.settings')->with('success', "Device session '{$deviceInfo['label']}' has been successfully logged out.");
    }

    public function resetDevice()
    {
        $user = Auth::user();
        
        if (!\Illuminate\Support\Facades\Schema::hasColumn('users', 'admin_device_token')) {
            return redirect()->route('admin.settings')->with('danger', 'Error: The "admin_device_token" column is missing in your database. Please run "php artisan migrate" on your server first to enable device lock security.');
        }

        $user->update(['admin_device_token' => null]);
        
        SscHelper::logActivity($user->id, 'ADMIN_DEVICE_RESET', 'Cleared registered device token. Ready for new device registration on next login.');
        
        return redirect()->route('admin.settings')->with('success', 'Your registered device has been successfully reset. Next time you log in, the new device will be registered.');
    }

    public function logoutOthers()
    {
        $user = Auth::user();
        
        if (!\Illuminate\Support\Facades\Schema::hasColumn('users', 'admin_device_token')) {
            return redirect()->route('admin.settings')->with('danger', 'Error: The "admin_device_token" column is missing in your database. Please run "php artisan migrate" on your server first to enable session revocation.');
        }
        
        // Regenerate the token
        $newToken = \Illuminate\Support\Str::random(60);
        $user->update(['admin_device_token' => $newToken]);
        
        // Set the new cookie for the current device so it stays authorized!
        cookie()->queue(cookie()->forever('admin_device_token', $newToken));

        // Delete other sessions from sessions table
        if (\Illuminate\Support\Facades\Schema::hasTable('sessions')) {
            DB::table('sessions')
                ->where('user_id', $user->id)
                ->where('id', '!=', session()->getId())
                ->delete();
        }
        
        SscHelper::logActivity($user->id, 'ADMIN_SESSIONS_REVOKED', 'Revoked all other active admin device sessions.');
        
        return redirect()->route('admin.settings')->with('success', 'All other active device sessions have been successfully terminated.');
    }

    public function registerCurrentDevice()
    {
        $user = Auth::user();
        
        if (!\Illuminate\Support\Facades\Schema::hasColumn('users', 'admin_device_token')) {
            return redirect()->route('admin.settings')->with('danger', 'Error: The "admin_device_token" column is missing in your database. Please run "php artisan migrate" on your server first to enable device registration.');
        }
        
        // Generate a new secure device token
        $token = \Illuminate\Support\Str::random(60);
        $user->update(['admin_device_token' => $token]);
        
        // Save to browser forever cookie
        cookie()->queue(cookie()->forever('admin_device_token', $token));
        
        SscHelper::logActivity($user->id, 'ADMIN_DEVICE_REGISTERED', 'Registered current device as primary device from Settings.');
        
        return redirect()->route('admin.settings')->with('success', 'Your current device has been successfully registered and locked in as your primary authorized device!');
    }

    public function addSchoolYear(Request $request)
    {
        $request->validate(['sy_label' => 'required|regex:/^\d{4}-\d{4}$/|unique:school_years,label']);
        SchoolYear::create(['label' => $request->sy_label, 'is_active' => 0]);
        return redirect()->route('admin.settings')->with('success', "School year '{$request->sy_label}' added.");
    }

    public function activateSchoolYear(SchoolYear $schoolYear)
    {
        SchoolYear::query()->update(['is_active' => 0]);
        $schoolYear->update(['is_active' => 1]);
        SscHelper::logActivity(Auth::id(), 'SETTINGS_CHANGE', "Changed active school year to {$schoolYear->label}");
        return redirect()->route('admin.settings')->with('success', 'Active school year updated.');
    }

    public function deleteSchoolYear(SchoolYear $schoolYear)
    {
        if ($schoolYear->is_active) {
            return redirect()->route('admin.settings')->with('danger', 'Cannot delete active school year.');
        }
        $schoolYear->delete();
        return redirect()->route('admin.settings')->with('success', 'School year deleted.');
    }

    public function toggleCandidacy(Request $request)
    {
        $activeSy = SchoolYear::where('is_active', 1)->first();
        if (!$activeSy) {
            return redirect()->route('admin.settings')->with('danger', 'No active school year set.');
        }

        $newStatus = !$activeSy->candidacy_open;
        $activeSy->update(['candidacy_open' => $newStatus]);

        $statusStr = $newStatus ? 'OPEN' : 'CLOSED';

        if ($newStatus) {
            \App\Models\Announcement::create([
                'title' => 'Filing for SSC Officer Candidacy is OPEN!',
                'content' => 'Attention Students! The Supreme Student Council is pleased to announce that candidacy filing for new SSC officers is now officially OPEN. If you want to run as a representative for your department, you can now submit your application via the student portal. Review instructions inside.',
                'created_by' => Auth::id()
            ]);
        } else {
            \App\Models\Announcement::create([
                'title' => 'Filing for SSC Officer Candidacy is CLOSED',
                'content' => 'Notice: Candidacy filing for the new SSC officers is now closed. Thank you to all the student leaders who submitted their applications. The respective deans will now review the candidates.',
                'created_by' => Auth::id()
            ]);
        }

        SscHelper::logActivity(Auth::id(), 'ELECTION_SETTINGS', "Candidacy filing is now {$statusStr} for SY {$activeSy->label}");

        return redirect()->route('admin.settings')->with('success', "Candidacy filing is now {$statusStr} and announcement has been auto-posted.");
    }

    public function export()
    {
        $database = DB::getDatabaseName();
        $tables = collect(DB::select('SHOW TABLES'))->map(function ($row) {
            return array_values((array) $row)[0];
        })->all();

        $sql = "-- SSC Database Backup\n";
        $sql .= sprintf("-- Database: %s\n", $database);
        $sql .= sprintf("-- Generated: %s\n\n", now()->toDateTimeString());
        $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

        foreach ($tables as $table) {
            $create = DB::selectOne("SHOW CREATE TABLE `{$table}`");
            $createSql = $create->{'Create Table'} ?? $create->{'Create View'} ?? null;
            if (!$createSql) {
                continue;
            }

            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql .= $createSql . ";\n\n";

            $rows = DB::table($table)->get();
            if ($rows->isEmpty()) {
                continue;
            }

            $columns = implode(', ', array_map(fn ($col) => "`{$col}`", array_keys((array) $rows->first())));
            foreach ($rows as $row) {
                $values = array_map(function ($value) {
                    return $this->escapeSqlValue($value);
                }, array_values((array) $row));
                $sql .= sprintf("INSERT INTO `%s` (%s) VALUES (%s);\n", $table, $columns, implode(', ', $values));
            }
            $sql .= "\n";
        }

        $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";

        SscHelper::logActivity(Auth::id(), 'DATA_EXPORT', 'Exported raw SQL database backup');

        return response($sql, 200, [
            'Content-Type' => 'application/sql; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="ssc_database_backup_' . date('Y_m_d_His') . '.sql"',
        ]);
    }

    private function escapeSqlValue($value)
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return "'" . str_replace([
            "\\",
            "\0",
            "\n",
            "\r",
            "\x1a",
            "'",
            '"',
        ], [
            "\\\\",
            "\\0",
            "\\n",
            "\\r",
            "\\Z",
            "\\'",
            '\\"',
        ], (string) $value) . "'";
    }
}
