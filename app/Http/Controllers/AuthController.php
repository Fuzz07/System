<?php

namespace App\Http\Controllers;

use App\Helpers\SscHelper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AuthController extends Controller
{
   
    private const MAX_ATTEMPTS = 5;


    private const DECAY_SECONDS = 600;

    public function showLogin(string $portal = 'student')
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }
        return view('auth.login', compact('portal'));
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'password' => 'required|max:255',
            'portal' => 'required|in:admin,officer,student,treasurer,dean',
        ]);

        if ($request->input('portal') !== 'student') {
            $request->validate([
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
            ], [
                'latitude.required' => 'Location coordinates are required to log in.',
                'longitude.required' => 'Location coordinates are required to log in.',
            ]);

            $lat = (float) $request->input('latitude');
            $lng = (float) $request->input('longitude');
            $ip  = $request->ip();

            // 1. Verify client coordinates
            if (!SscHelper::isWithinPhilippines($lat, $lng)) {
                SscHelper::logActivity(null, 'LOGIN_BLOCKED_GEO', "Access denied: Login attempt from outside the Philippines (Lat: {$lat}, Lng: {$lng}) for email: {$request->email}");
                return back()->withErrors([
                    'email' => 'Access denied: You are attempting to log in from outside the Philippines.',
                ])->withInput();
            }

            // 2. Verify server-side IP address to prevent HTTP body/script coordinate tampering bypasses
            if (!SscHelper::isIpInPhilippines($ip)) {
                SscHelper::logActivity(null, 'LOGIN_BLOCKED_GEO_SPOOF', "Access denied: IP {$ip} originates outside the Philippines despite submitted coordinates (Lat: {$lat}, Lng: {$lng}) for email: {$request->email}");
                return back()->withErrors([
                    'email' => 'Access denied: Your network IP address is located outside the Philippines.',
                ])->withInput();
            }
        }

        // ── Brute-Force / Rate-Limit Check ──────────────────────────────────
        $throttleKey = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            $secondsLeft = RateLimiter::availableIn($throttleKey);
            $minutesLeft = ceil($secondsLeft / 60);

            SscHelper::logActivity(null, 'LOGIN_BLOCKED', "Rate-limited login attempt for {$request->email} from {$request->ip()}");

            return back()->withErrors([
                'email' => "Too many failed login attempts. Please try again in {$minutesLeft} minute(s).",
            ])->withInput()->with('lockout_seconds', $secondsLeft);
        }


        if (!CaptchaController::verifyToken($request->input('captcha_verified_token'))) {
            RateLimiter::hit($throttleKey, self::DECAY_SECONDS);
            return back()->withErrors(['email' => 'Security check failed. Please verify that you are not a robot.'])->withInput();
        }


        $portal = $request->portal;
        $allowedRoles = match ($portal) {
            'admin' => ['admin'],
            'treasurer' => ['treasurer'],
            'officer' => ['officer'],
            'student' => ['student'],
            'dean' => ['dean'],
        };


        $user = User::where('email', $request->email)
            ->whereIn('role', $allowedRoles)
            ->first();  


        if ($user && $user->isStudent() && config('ssc.auto_deactivate_graduates', true) && $user->isGraduated()) {
            $user->update(['status' => 'inactive']);
            SscHelper::logActivity(null, 'STUDENT_AUTO_DEACTIVATE', "Auto-deactivated graduated student: {$user->email}");

            return back()->withErrors(['email' => 'Your account has been set to inactive due to graduation. Please contact the administrator if this is incorrect.']);
        }

        if (!$user || !Hash::check($request->password, $user->password)) {

            RateLimiter::hit($throttleKey, self::DECAY_SECONDS);

            $attemptsLeft = self::MAX_ATTEMPTS - RateLimiter::attempts($throttleKey);

            SscHelper::logActivity(
                $user?->id ?? null,
                'LOGIN_FAILED',
                "Failed login attempt for {$request->email} via {$portal} portal"
            );

            $message = 'Invalid credentials or unauthorized portal access.';
            if ($attemptsLeft <= 2 && $attemptsLeft > 0) {
                $message .= " Warning: {$attemptsLeft} attempt(s) remaining before your account is temporarily locked.";
            }

            return back()->withErrors(['email' => $message])->withInput();
        }


        if ($user->status !== 'active') {
            RateLimiter::hit($throttleKey, self::DECAY_SECONDS);
            return back()->withErrors(['email' => 'Your account is not active. Please wait for admin approval.'])->withInput();
        }


        RateLimiter::clear($throttleKey);
        session()->forget('captcha_token');

        if ($user->isAdmin()) {
            // ── Device Restriction Check ──────────────────
            if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'admin_device_token')) {
                $registeredToken = $user->admin_device_token;
                $cookieToken = request()->cookie('admin_device_token');

                if (!empty($registeredToken) && $cookieToken !== $registeredToken) {
                    // Instead of blocking, we initiate a Device Login Approval Request!
                    $approvalId = \Illuminate\Support\Str::random(32);
                    $tempToken = \Illuminate\Support\Str::random(60);
                    $otp = (string) rand(100000, 999999);

                    $requestData = [
                        'id' => $approvalId,
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'ip' => $request->ip(),
                        'latitude' => $request->input('latitude'),
                        'longitude' => $request->input('longitude'),
                        'user_agent' => $request->userAgent() ?? 'Unknown Browser',
                        'status' => 'pending',
                        'temp_device_token' => $tempToken,
                        'otp' => $otp,
                        'created_at' => now(),
                    ];

                    // Store details in Cache for 5 minutes
                    \Illuminate\Support\Facades\Cache::put("admin_login_approval_{$approvalId}", $requestData, now()->addMinutes(5));

                    // Append to admin's pending approvals list
                    $userPendingKey = "admin_pending_approvals_{$user->id}";
                    $pendingList = \Illuminate\Support\Facades\Cache::get($userPendingKey, []);
                    $pendingList[] = $approvalId;
                    \Illuminate\Support\Facades\Cache::put($userPendingKey, $pendingList, now()->addMinutes(5));

                    // Log activity
                    SscHelper::logActivity($user->id, 'LOGIN_APPROVAL_REQUEST', "Unrecognized device login approval initiated for {$user->email} from IP {$request->ip()}");

                    $host = request()->getHost();
                    if (str_starts_with($host, 'admin.')) {
                        $waitingRoute = route('admin.login.approval_waiting', $approvalId);
                    } else {
                        $waitingRoute = route('admin.login.approval_waiting.main', $approvalId);
                    }

                    return redirect()->to($waitingRoute);
                }
            }

            // ── Generate and Send OTP ─────────────────────
            $otp = (string) rand(100000, 999999);
            session([
                'admin_login_user_id' => $user->id,
                'admin_login_otp' => $otp,
                'admin_login_otp_expires_at' => now()->addMinutes(3),
                'admin_login_latitude' => $request->input('latitude'),
                'admin_login_longitude' => $request->input('longitude'),
            ]);

            try {
                Mail::send([], [], function ($message) use ($user, $otp) {
                    $message->to($user->email)
                        ->subject('Your Admin Login Verification Code')
                        ->html("
                            <div style='font-family: sans-serif; padding: 20px; max-width: 600px; margin: auto; border: 1px solid #e2e8f0; border-radius: 8px;'>
                                <h2 style='color: #0f172a; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;'>SSC Admin Portal Verification</h2>
                                <p style='color: #334155; font-size: 16px;'>You are attempting to log in to the SSC Admin Portal. Please use the following secure 6-digit verification code to complete your login:</p>
                                <div style='background: #f1f5f9; padding: 15px; border-radius: 6px; text-align: center; font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #1e3a8a; margin: 20px 0;'>{$otp}</div>
                                <p style='color: #64748b; font-size: 14px;'>This code is valid for 3 minutes. If you did not request this login attempt, please change your password and secure your account immediately.</p>
                            </div>
                        ");
                });
            } catch (\Exception $e) {
                Log::error('Admin OTP email failed to send', ['error' => $e->getMessage()]);
            }

            // Check if current domain is admin subdomain or main domain
            $host = request()->getHost();
            if (str_starts_with($host, 'admin.')) {
                $otpRoute = route('admin.login.otp');
            } else {
                $otpRoute = route('admin.login.otp.main');
            }

            return redirect()->to($otpRoute)->with('success', 'A secure 6-digit verification code has been sent to your admin email address.');
        }


        $isAndroidApp = str_contains(request()->userAgent() ?? '', 'SSCStudentApp');
        Auth::login($user, $isAndroidApp);
        $request->session()->regenerate();
        $request->session()->save();

        $logDetails = "Logged in via {$portal} portal";
        if ($portal !== 'student') {
            $lat = $request->input('latitude');
            $lng = $request->input('longitude');
            $logDetails .= " | Location: Lat {$lat}, Lng {$lng}";
        }
        SscHelper::logActivity($user->id, 'LOGIN', $logDetails);

        return $this->redirectByRole($user, true);
    }

    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'age' => 'required|integer|min:10|max:100',
            'year_level' => 'required|string',
            'department' => 'required|string|max:100',
            'student_id' => 'required|string|regex:/^\d{4}-\d{4}$/',
            'email' => 'required|email|max:255|unique:users,email|ends_with:@mcclawis.edu.ph',

            'password' => ['required', 'min:8', 'confirmed', 'regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/'],
        ], [
            'email.unique' => 'This Microsoft 365 school account is already registered under another student\'s profile. Please log in or use Forgot Password.',
            'password.regex' => 'Password must contain at least one letter and one number.',
            'password.min' => 'Password must be at least 8 characters.',
        ]);

        $sessionVerified = session('register_email_verified');
        $sessionEmail = session('register_email');

        if (!$sessionVerified || $sessionEmail !== $request->email) {
            return back()->withErrors(['email' => 'Please verify your Microsoft 365 school account email address before creating your password.'])->withInput();
        }

        if (!CaptchaController::verifyToken($request->input('captcha_verified_token'))) {
            return back()->withErrors(['email' => 'Security check failed. Please verify that you are not a robot.'])->withInput();
        }

        $fullname = trim($request->first_name . ' ' . ($request->middle_name ?? '') . ' ' . $request->last_name);

        $user = User::create([
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'age' => $request->age,
            'year_level' => $request->year_level,
            'department' => $request->department,
            'student_id' => $request->student_id,
            'fullname' => $fullname,
            'email' => $request->email,
            'password' => $request->password,
            'role' => 'student',
            'status' => 'inactive',
        ]);

        SscHelper::logActivity($user->id, 'REGISTER', "Student registered and email verified via OTP: {$user->email}");


        session()->forget(['register_otp', 'register_email', 'register_email_verified']);


        return view('auth.confirm-success', compact('user'));
    }

    public function confirmAccount(Request $request, User $user)
    {
        if (!$request->hasValidSignature()) {
            abort(401, 'This confirmation link is invalid or has expired.');
        }

        SscHelper::logActivity($user->id, 'ACTIVATE_EMAIL', "Student email confirmed successfully: {$user->email}");


        return view('auth.confirm-success', compact('user'));
    }

    public function checkEmail(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|ends_with:@mcclawis.edu.ph',
            ], [
                'email.ends_with' => 'The email address must belong to the @mcclawis.edu.ph domain.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first('email'),
            ]);
        }


        if (User::where('email', $request->email)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This Microsoft 365 school account is already registered under another student\'s profile. Please log in or use Forgot Password.',
            ]);
        }


        try {
            $msResponse = Http::timeout(6)
                ->post('https://login.microsoftonline.com/common/GetCredentialType', [
                    'Username' => $request->email
                ]);

            if ($msResponse->successful()) {
                $ifExistsResult = $msResponse->json('IfExistsResult');
                if ($ifExistsResult === 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This Microsoft 365 account does not exist. Please double-check your school email address spelling or contact the school IT administrator.',
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::warning('MS Account check failed during AJAX checkEmail', ['error' => $e->getMessage()]);

        }


        $otp = (string) rand(100000, 999999);
        session([
            'register_otp' => $otp,
            'register_email' => $request->email,
            'register_otp_expires_at' => now()->addMinutes(3),
        ]);


        try {
            Mail::send([], [], function ($message) use ($request, $otp) {
                $message->to($request->email)
                    ->subject('Your SSC Account Verification Code')
                    ->html(view('auth.emails.otp', ['otp' => $otp])->render());
            });
            return response()->json([
                'success' => true,
                'message' => 'Verification code sent! Please check your Microsoft school email inbox (or spam folder) for the 6-digit code.',
            ]);
        } catch (\Exception $e) {
            Log::error('OTP email failed to send', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send verification email. Please try again later or contact support.',
            ]);
        }
    }

    public function verifyOtp(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'otp' => 'required|string|size:6',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'The code must be exactly 6 characters.',
            ]);
        }

        $sessionOtp = session('register_otp');
        $sessionEmail = session('register_email');
        $expiresAt = session('register_otp_expires_at');

        if ($expiresAt && now()->greaterThan($expiresAt)) {
            session()->forget(['register_otp', 'register_email', 'register_otp_expires_at']);
            return response()->json([
                'success' => false,
                'message' => 'Verification code has expired (valid for 3 minutes). Please request a new code.'
            ]);
        }

        if ($request->otp === $sessionOtp && $request->email === $sessionEmail) {
            session(['register_email_verified' => true]);
            return response()->json([
                'success' => true,
                'message' => 'Email verified successfully! Proceeding to password creation.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid or expired verification code. Please check your email and try again.'
        ]);
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            SscHelper::logActivity(Auth::id(), 'LOGOUT', 'User logged out');
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    protected function redirectByRole($user, $justLoggedIn = false)
    {
        $redirect = null;
        if ($user->role === 'student') {
            $ua = request()->userAgent() ?? '';
            $isMobile = preg_match('/Mobile|Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i', $ua);
            if ($isMobile) {
                $redirect = redirect()->route('mobile.student.proposals');
            } else {
                $redirect = redirect()->route('student.proposals');
                if ($justLoggedIn) {
                    $redirect = $redirect->with('show_app_download', true);
                }
            }
        } else {
            $redirect = match ($user->role) {
                'admin' => redirect()->route('admin.dashboard'),
                'treasurer' => redirect()->route('treasurer.dashboard'),
                'officer' => redirect()->route('officer.dashboard'),
                'dean' => redirect()->route('dean.dashboard'),
                default => redirect('/'),
            };
        }

        return $redirect->with('success', 'Welcome back, ' . $user->fullname . '! You have successfully signed into the portal.');
    }


    private function throttleKey(Request $request): string
    {
        return 'login|' . Str::lower($request->input('email', '')) . '|' . $request->ip();
    }



    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|ends_with:@mcclawis.edu.ph',
        ], [
            'email.ends_with' => 'The email address must belong to the @mcclawis.edu.ph domain.',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'This Microsoft 365 school account is not registered. Please sign up first.'])->withInput();
        }


        $otp = (string) rand(100000, 999999);
        session([
            'reset_password_otp' => $otp,
            'reset_password_email' => $request->email,
            'reset_password_otp_expires_at' => now()->addMinutes(3),
        ]);


        try {
            Mail::send([], [], function ($message) use ($request, $otp) {
                $message->to($request->email)
                    ->subject('Your SSC Password Reset Verification Code')
                    ->html(view('auth.emails.reset-password', ['otp' => $otp])->render());
            });
            return redirect()->route('password.reset')->with('success', 'Verification code sent! Please check your Outlook/school email inbox for the 6-digit password reset code.');
        } catch (\Exception $e) {
            Log::error('Reset password email failed to send', ['error' => $e->getMessage()]);

            return back()->withErrors(['email' => 'Failed to send password reset email. Please try again later or contact support.'])->withInput();
        }
    }

    public function showResetPassword()
    {
        return view('auth.reset-password');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|ends_with:@mcclawis.edu.ph',
            'otp' => 'required|string|size:6',
            'password' => ['required', 'min:8', 'confirmed', 'regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/'],
        ], [
            'otp.size' => 'The verification code must be exactly 6 digits.',
            'password.regex' => 'Password must contain at least one letter and one number.',
            'password.min' => 'Password must be at least 8 characters.',
        ]);

        $sessionOtp = session('reset_password_otp');
        $sessionEmail = session('reset_password_email');
        $expiresAt = session('reset_password_otp_expires_at');

        if ($expiresAt && now()->greaterThan($expiresAt)) {
            session()->forget(['reset_password_otp', 'reset_password_email', 'reset_password_otp_expires_at']);
            return back()->withErrors(['otp' => 'Verification code has expired (valid for 3 minutes). Please request a new code.'])->withInput();
        }

        if ($request->otp !== $sessionOtp || $request->email !== $sessionEmail) {
            return back()->withErrors(['otp' => 'Invalid or expired verification code. Please check your email and try again.'])->withInput();
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->withErrors(['email' => 'User record not found.'])->withInput();
        }

        // Update the password
        $user->update([
            'password' => $request->password, // automatically hashed by User model casts
        ]);

        // Clear password reset session keys
        session()->forget(['reset_password_otp', 'reset_password_email']);

        SscHelper::logActivity($user->id, 'PASSWORD_RESET', "Reset password for: {$user->email}");

        return redirect()->route('login')->with('success', 'Your password has been successfully reset! You can now log in with your new password.');
    }

    public function showAdminOtp()
    {
        $expiresAt = session('admin_login_otp_expires_at');
        if (!session()->has('admin_login_otp') || !session()->has('admin_login_user_id') || ($expiresAt && now()->greaterThan($expiresAt))) {
            session()->forget(['admin_login_otp', 'admin_login_otp_expires_at', 'admin_login_user_id', 'admin_login_latitude', 'admin_login_longitude']);
            return redirect()->route('login', ['portal' => 'admin'])->withErrors(['email' => 'The verification code has expired. Please log in again.']);
        }

        $expiresTimestamp = $expiresAt ? $expiresAt->timestamp : now()->addMinutes(3)->timestamp;
        return view('auth.admin-otp', compact('expiresTimestamp'));
    }

    public function verifyAdminOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ], [
            'otp.size' => 'The verification code must be exactly 6 digits.',
        ]);

        $sessionOtp = session('admin_login_otp');
        $expiresAt = session('admin_login_otp_expires_at');
        $userId = session('admin_login_user_id');

        if (!$sessionOtp || !$userId || now()->greaterThan($expiresAt)) {
            session()->forget(['admin_login_otp', 'admin_login_otp_expires_at', 'admin_login_user_id']);
            return redirect()->route('login', ['portal' => 'admin'])->withErrors(['email' => 'The verification code has expired. Please log in again.']);
        }

        if ($request->otp !== $sessionOtp) {
            return back()->withErrors(['otp' => 'Invalid verification code. Please try again.'])->withInput();
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('login', ['portal' => 'admin'])->withErrors(['email' => 'User not found.']);
        }

        // Manage Device Token
        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'admin_device_token')) {
            $cookieToken = $request->cookie('admin_device_token');
            if (empty($user->admin_device_token)) {
                // First time registration of this device
                $token = \Illuminate\Support\Str::random(60);
                $user->update(['admin_device_token' => $token]);
                // Store cookie forever (5 years)
                cookie()->queue(cookie()->forever('admin_device_token', $token));
            } else {
                // Ensure cookie matches the existing token
                if ($cookieToken !== $user->admin_device_token) {
                    // Set the cookie again just in case it was lost but they managed to verify OTP
                    cookie()->queue(cookie()->forever('admin_device_token', $user->admin_device_token));
                }
            }
        } else {
            \Illuminate\Support\Facades\Log::warning("admin_device_token column is missing in users table. Please run 'php artisan migrate' to enable device restriction security.");
        }

        // Log the admin in
        Auth::login($user);
        $request->session()->regenerate();

        // Log Activity
        $lat = session('admin_login_latitude');
        $lng = session('admin_login_longitude');
        $logDetails = "Logged in via admin portal with OTP and Device Verification";
        if (!empty($lat) && !empty($lng)) {
            $logDetails .= " | Location: Lat {$lat}, Lng {$lng}";
        }
        SscHelper::logActivity($user->id, 'LOGIN', $logDetails);

        // Clear Admin Login session
        session()->forget(['admin_login_otp', 'admin_login_otp_expires_at', 'admin_login_user_id', 'admin_login_latitude', 'admin_login_longitude']);

        return redirect()->route('admin.dashboard')->with('success', 'Successfully authenticated and device registered.');
    }

    public function showApprovalWaiting($approvalId)
    {
        $requestData = \Illuminate\Support\Facades\Cache::get("admin_login_approval_{$approvalId}");
        if (!$requestData) {
            return redirect()->route('login', ['portal' => 'admin'])->withErrors(['email' => 'Authorization request expired or invalid. Please try again.']);
        }

        return view('auth.approval-waiting', compact('approvalId', 'requestData'));
    }

    public function checkApprovalStatus($approvalId)
    {
        $requestData = \Illuminate\Support\Facades\Cache::get("admin_login_approval_{$approvalId}");
        if (!$requestData) {
            return response()->json(['status' => 'expired']);
        }

        return response()->json(['status' => $requestData['status']]);
    }

    public function completeApprovalLogin($approvalId)
    {
        $requestData = \Illuminate\Support\Facades\Cache::get("admin_login_approval_{$approvalId}");
        if (!$requestData || $requestData['status'] !== 'approved') {
            return redirect()->route('login', ['portal' => 'admin'])->withErrors(['email' => 'Authorization request was declined or expired.']);
        }

        $user = User::find($requestData['user_id']);
        if (!$user) {
            return redirect()->route('login', ['portal' => 'admin'])->withErrors(['email' => 'User not found.']);
        }

        // Register the new device token
        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'admin_device_token')) {
            $user->update(['admin_device_token' => $requestData['temp_device_token']]);
            cookie()->queue(cookie()->forever('admin_device_token', $requestData['temp_device_token']));
        }

        // Log the admin in
        Auth::login($user);
        request()->session()->regenerate();

        // Log Activity
        $lat = $requestData['latitude'];
        $lng = $requestData['longitude'];
        $logDetails = "Logged in via Admin Device Login Approval";
        if (!empty($lat) && !empty($lng)) {
            $logDetails .= " | Location: Lat {$lat}, Lng {$lng}";
        }
        SscHelper::logActivity($user->id, 'LOGIN', $logDetails);

        // Clear the cache key
        \Illuminate\Support\Facades\Cache::forget("admin_login_approval_{$approvalId}");

        return redirect()->route('admin.dashboard')->with('success', 'Logged in and new device authorized successfully.');
    }

    public function triggerOtpFallback($approvalId)
    {
        $requestData = \Illuminate\Support\Facades\Cache::get("admin_login_approval_{$approvalId}");
        if (!$requestData) {
            return redirect()->route('login', ['portal' => 'admin'])->withErrors(['email' => 'Authorization request expired. Please try again.']);
        }

        $user = User::find($requestData['user_id']);
        if (!$user) {
            return redirect()->route('login', ['portal' => 'admin'])->withErrors(['email' => 'User not found.']);
        }

        // Send OTP to email
        $otp = $requestData['otp'];
        session([
            'admin_login_user_id' => $user->id,
            'admin_login_otp' => $otp,
            'admin_login_otp_expires_at' => now()->addMinutes(3),
            'admin_login_latitude' => $requestData['latitude'],
            'admin_login_longitude' => $requestData['longitude'],
        ]);

        try {
            \Illuminate\Support\Facades\Mail::send([], [], function ($message) use ($user, $otp) {
                $message->to($user->email)
                    ->subject('Your Admin Login Verification Code')
                    ->html("
                        <div style='font-family: sans-serif; padding: 20px; max-width: 600px; margin: auto; border: 1px solid #e2e8f0; border-radius: 8px;'>
                            <h2 style='color: #0f172a; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;'>SSC Admin Portal Verification</h2>
                            <p style='color: #334155; font-size: 16px;'>You are attempting to log in to the SSC Admin Portal. Please use the following secure 6-digit verification code to complete your login:</p>
                            <div style='background: #f1f5f9; padding: 15px; border-radius: 6px; text-align: center; font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #1e3a8a; margin: 20px 0;'>{$otp}</div>
                            <p style='color: #64748b; font-size: 14px;'>This code is valid for 3 minutes. If you did not request this login attempt, please change your password immediately.</p>
                        </div>
                    ");
            });
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Admin OTP fallback email failed to send', ['error' => $e->getMessage()]);
        }

        // Clear approval cache
        \Illuminate\Support\Facades\Cache::forget("admin_login_approval_{$approvalId}");

        $host = request()->getHost();
        if (str_starts_with($host, 'admin.')) {
            $otpRoute = route('admin.login.otp');
        } else {
            $otpRoute = route('admin.login.otp.main');
        }

        return redirect()->to($otpRoute)->with('success', 'A secure 6-digit verification code has been sent to your email address.');
    }
}
