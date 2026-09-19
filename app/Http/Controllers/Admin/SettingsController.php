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

        $maintenanceData = \App\Helpers\MaintenanceHelper::getData();

        return view('admin.settings', compact('schoolYears', 'dbStats', 'activeSessions', 'maintenanceData'));
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
        $validated = $request->validate([
            'sy_label' => ['required', 'regex:/^\d{4}-\d{4}$/', 'unique:school_years,label'],
            'semester' => ['required', \Illuminate\Validation\Rule::in(array_keys(SchoolYear::SEMESTERS))],
        ]);

        SchoolYear::create([
            'label' => $validated['sy_label'],
            'semester' => $validated['semester'],
            'is_active' => false,
        ]);

        $semesterLabel = SchoolYear::SEMESTERS[$validated['semester']];

        return redirect()->route('admin.settings')->with('success', "School year '{$validated['sy_label']}' ({$semesterLabel}) added.");
    }

    public function activateSchoolYear(Request $request, SchoolYear $schoolYear)
    {
        $validated = $request->validate([
            'semester' => ['required', \Illuminate\Validation\Rule::in(array_keys(SchoolYear::SEMESTERS))],
        ]);

        SchoolYear::query()->update(['is_active' => 0]);
        $schoolYear->update([
            'is_active' => 1,
            'semester' => $validated['semester'],
        ]);

        // ── Keep SSC_CURRENT_SCHOOL_YEAR .env key in sync ──────────────────
        // This ensures any code still reading config('ssc.current_school_year')
        // (e.g. legacy references) stays consistent with the DB-driven active SY.
        $envPath = base_path('.env');
        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);
            $newLabel   = $schoolYear->label;

            if (str_contains($envContent, 'SSC_CURRENT_SCHOOL_YEAR=')) {
                $envContent = preg_replace(
                    '/^SSC_CURRENT_SCHOOL_YEAR=.*/m',
                    'SSC_CURRENT_SCHOOL_YEAR=' . $newLabel,
                    $envContent
                );
            } else {
                $envContent .= "\nSSC_CURRENT_SCHOOL_YEAR=" . $newLabel . "\n";
            }
            file_put_contents($envPath, $envContent);
        }

        $semesterLabel = $schoolYear->semester_label;
        SscHelper::logActivity(Auth::id(), 'SETTINGS_CHANGE', "Changed active academic term to {$schoolYear->label} - {$semesterLabel}");

        return redirect()->route('admin.settings')->with(
            'success',
            "Active academic term updated to {$schoolYear->label} - {$semesterLabel}. Enrollment payment records now use this semester."
        );
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

    public function requestExportOtp(Request $request)
    {
        $user = Auth::user();
        $otp = (string) random_int(100000, 999999);

        session([
            'export_sql_otp' => $otp,
            'export_sql_otp_expires_at' => now()->addMinutes(3),
        ]);

        try {
            \Illuminate\Support\Facades\Mail::send([], [], function ($message) use ($user, $otp) {
                $message->to($user->email)
                    ->subject('SQL Database Export Verification Code')
                    ->html("
                        <div style='font-family: sans-serif; padding: 20px; max-width: 600px; margin: auto; border: 1px solid #cbd5e1; border-radius: 8px;'>
                            <h2 style='color: #0f172a; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;'>SSC Admin Data Export Security</h2>
                            <p style='color: #334155; font-size: 16px;'>You have requested to download a full SQL database backup of the SSC system. Please enter the following 6-digit security verification code to authorize this download:</p>
                            <div style='background: #f1f5f9; padding: 15px; border-radius: 6px; text-align: center; font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #1e3a8a; margin: 20px 0;'>{$otp}</div>
                            <p style='color: #64748b; font-size: 14px;'>This code is valid for 3 minutes. If you did not initiate this export request, please secure your admin account immediately.</p>
                        </div>
                    ");
            });

            return response()->json([
                'success' => true,
                'message' => 'Verification code sent to ' . $user->email . '! Check your email inbox.',
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Export OTP email failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send verification code email. Please try again.',
            ], 500);
        }
    }

    public function export(Request $request)
    {
        $sessionOtp = session('export_sql_otp');
        $expiresAt = session('export_sql_otp_expires_at');
        $inputOtp = trim($request->input('otp', ''));

        if (empty($sessionOtp) || empty($expiresAt) || now()->greaterThan($expiresAt) || $inputOtp !== $sessionOtp) {
            return redirect()->route('admin.settings')->with('danger', 'Security Check Failed: Invalid or expired OTP verification code for SQL backup export.');
        }

        // Clear OTP session
        session()->forget(['export_sql_otp', 'export_sql_otp_expires_at']);

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

        SscHelper::logActivity(Auth::id(), 'DATA_EXPORT', 'Exported raw SQL database backup with OTP verification');

        return response($sql, 200, [
            'Content-Type' => 'application/sql; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="ssc_database_backup_' . date('Y_m_d_His') . '.sql"',
        ]);
    }

    public function requestMaintenanceOtp(Request $request)
    {
        $user = Auth::user();
        $targetAction = $request->input('target_action') === 'disable' ? 'disable' : 'enable';
        $otp = (string) random_int(100000, 999999);

        session([
            'maintenance_mode_otp' => $otp,
            'maintenance_mode_otp_action' => $targetAction,
            'maintenance_mode_otp_expires_at' => now()->addMinutes(3),
        ]);

        $actionLabel = $targetAction === 'enable' ? 'ENABLE' : 'DISABLE';
        $actionDesc = $targetAction === 'enable'
            ? 'lock down public/student portals and activate Maintenance Mode'
            : 'restore public and student access to normal operational status';

        try {
            \Illuminate\Support\Facades\Mail::send([], [], function ($message) use ($user, $otp, $actionLabel, $actionDesc) {
                $message->to($user->email)
                    ->subject("Maintenance Mode {$actionLabel} Security Verification Code")
                    ->html("
                        <div style='font-family: sans-serif; padding: 24px; max-width: 600px; margin: auto; border: 1px solid #cbd5e1; border-radius: 12px; background: #ffffff;'>
                            <h2 style='color: #0f172a; border-bottom: 2px solid #e2e8f0; padding-bottom: 12px; margin-top: 0;'>SSC Admin Security Authorization</h2>
                            <p style='color: #334155; font-size: 15px; line-height: 1.6;'>
                                You requested to <strong>{$actionLabel}</strong> System Maintenance Mode ({$actionDesc}).
                            </p>
                            <p style='color: #334155; font-size: 15px;'>Please enter the following 6-digit verification code to authorize this action:</p>
                            <div style='background: #f8fafc; border: 2px dashed #cbd5e1; padding: 18px; border-radius: 8px; text-align: center; font-size: 36px; font-weight: bold; letter-spacing: 6px; color: #1e3a8a; margin: 20px 0;'>{$otp}</div>
                            <p style='color: #64748b; font-size: 13px; line-height: 1.5;'>
                                This verification code is valid for 3 minutes. If you did not initiate this change, please sign in and secure your administrator account immediately.
                            </p>
                        </div>
                    ");
            });

            return response()->json([
                'success' => true,
                'message' => 'Verification code sent to ' . $user->email . '! Check your email inbox.',
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Maintenance OTP email failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send verification code email. Please try again.',
            ], 500);
        }
    }

    public function toggleMaintenance(Request $request)
    {
        $sessionOtp = session('maintenance_mode_otp');
        $expectedAction = session('maintenance_mode_otp_action');
        $expiresAt = session('maintenance_mode_otp_expires_at');
        $inputOtp = trim($request->input('otp', ''));
        $targetAction = $request->input('action', $expectedAction);

        if (empty($sessionOtp) || empty($expiresAt) || now()->greaterThan($expiresAt) || $inputOtp !== $sessionOtp) {
            return redirect()->route('admin.settings')->with('danger', 'Security Check Failed: Invalid or expired OTP verification code for Maintenance Mode.');
        }

        // Clear OTP session
        session()->forget(['maintenance_mode_otp', 'maintenance_mode_otp_action', 'maintenance_mode_otp_expires_at']);

        $user = Auth::user();

        if ($targetAction === 'enable') {
            $customMessage = trim($request->input('message', ''));
            \App\Helpers\MaintenanceHelper::enable($user->id, $user->fullname, $customMessage ?: null);
            SscHelper::logActivity($user->id, 'MAINTENANCE_ENABLED', 'Activated System Maintenance Mode');
            return redirect()->route('admin.settings')->with('success', 'System Maintenance Mode has been ACTIVATED. Non-admin users are now shown the maintenance screen.');
        } else {
            \App\Helpers\MaintenanceHelper::disable();
            SscHelper::logActivity($user->id, 'MAINTENANCE_DISABLED', 'Deactivated System Maintenance Mode');
            return redirect()->route('admin.settings')->with('success', 'System Maintenance Mode has been DEACTIVATED. The system is now fully operational.');
        }
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
