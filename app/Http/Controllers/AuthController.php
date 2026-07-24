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
    /**
     * Maximum login attempts before lockout.
     */
    private const MAX_ATTEMPTS = 5;

    /**
     * Lockout duration in seconds (10 minutes).
     */
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
            'email'    => 'required|email|max:255',
            'password' => 'required|max:255',
            'portal'   => 'required|in:admin,officer,student,treasurer,dean',
        ]);

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

        // ── CAPTCHA Verification (stateless HMAC token) ──────────────────────
        if (! CaptchaController::verifyToken($request->input('captcha_verified_token'))) {
            RateLimiter::hit($throttleKey, self::DECAY_SECONDS);
            return back()->withErrors(['email' => 'Security check failed. Please verify that you are not a robot.'])->withInput();
        }

        // ── Role-Portal Mapping ──────────────────────────────────────────────
        $portal = $request->portal;
        $allowedRoles = match ($portal) {
            'admin'     => ['admin'],
            'treasurer' => ['treasurer'],
            'officer'   => ['officer'],
            'student'   => ['student'],
            'dean'      => ['dean'],
        };

        // Fetch user regardless of status so we can detect graduated accounts
        $user = User::where('email', $request->email)
            ->whereIn('role', $allowedRoles)
            ->first();

        // Auto-deactivate graduated students if configured
        if ($user && $user->isStudent() && config('ssc.auto_deactivate_graduates', true) && $user->isGraduated()) {
            $user->update(['status' => 'inactive']);
            SscHelper::logActivity(null, 'STUDENT_AUTO_DEACTIVATE', "Auto-deactivated graduated student: {$user->email}");

            return back()->withErrors(['email' => 'Your account has been set to inactive due to graduation. Please contact the administrator if this is incorrect.']);
        }

        if (!$user || !Hash::check($request->password, $user->password)) {
            // Increment the rate limiter on failure
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

        // Prevent login for inactive accounts
        if ($user->status !== 'active') {
            RateLimiter::hit($throttleKey, self::DECAY_SECONDS);
            return back()->withErrors(['email' => 'Your account is not active. Please wait for admin approval.'])->withInput();
        }

        // ── Successful Login ─────────────────────────────────────────────────
        RateLimiter::clear($throttleKey);
        session()->forget('captcha_token');

        // Automatically issue a persistent remember cookie for users logging in from the mobile app
        $isAndroidApp = str_contains(request()->userAgent() ?? '', 'SSCStudentApp');
        Auth::login($user, $isAndroidApp);
        $request->session()->regenerate();

        SscHelper::logActivity($user->id, 'LOGIN', "Logged in via {$portal} portal");

        return $this->redirectByRole($user, true);
    }

    public function register(Request $request)
    {
        $request->validate([
            'first_name'  => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name'   => 'required|string|max:100',
            'age'         => 'required|integer|min:10|max:100',
            'year_level'  => 'required|string',
            'department'  => 'required|string|max:100',
            'student_id'  => 'required|string|regex:/^\d{4}-\d{4}$/',
            'email'       => 'required|email|max:255|unique:users,email|ends_with:@mcclawis.edu.ph',
            // Password must be at least 8 characters and contain letters and numbers
            'password'    => ['required', 'min:8', 'confirmed', 'regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/'],
        ], [
            'email.unique'   => 'This school email address is already registered. Please log in instead.',
            'password.regex' => 'Password must contain at least one letter and one number.',
            'password.min'   => 'Password must be at least 8 characters.',
        ]);

        $sessionVerified = session('register_email_verified');
        $sessionEmail = session('register_email');

        if (! $sessionVerified || $sessionEmail !== $request->email) {
            return back()->withErrors(['email' => 'Please verify your Microsoft 365 school account email address before creating your password.'])->withInput();
        }

        if (! CaptchaController::verifyToken($request->input('captcha_verified_token'))) {
            return back()->withErrors(['email' => 'Security check failed. Please verify that you are not a robot.'])->withInput();
        }

        $fullname = trim($request->first_name . ' ' . ($request->middle_name ?? '') . ' ' . $request->last_name);

        $user = User::create([
            'first_name'  => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name'   => $request->last_name,
            'age'         => $request->age,
            'year_level'  => $request->year_level,
            'department'  => $request->department,
            'student_id'  => $request->student_id,
            'fullname'    => $fullname,
            'email'       => $request->email,
            'password'    => $request->password,
            'role'        => 'student',
            'status'      => 'inactive',
        ]);

        SscHelper::logActivity($user->id, 'REGISTER', "Student registered and email verified via OTP: {$user->email}");

        // Clear verification session keys
        session()->forget(['register_otp', 'register_email', 'register_email_verified']);

        // Redirect directly to our beautiful success page!
        return view('auth.confirm-success', compact('user'));
    }

    public function confirmAccount(Request $request, User $user)
    {
        if (! $request->hasValidSignature()) {
            abort(401, 'This confirmation link is invalid or has expired.');
        }

        SscHelper::logActivity($user->id, 'ACTIVATE_EMAIL', "Student email confirmed successfully: {$user->email}");

        // Render the beautiful confirmation success view passing the student details
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

        // Check if user already exists
        if (User::where('email', $request->email)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This school email address is already registered. Please log in.',
            ]);
        }

        // Validate MS Account existence
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
            // Gracefully succeed if MS API is offline to prevent blocking students
        }

        // Generate a 6-digit OTP and save in session
        $otp = (string) rand(100000, 999999);
        session(['register_otp' => $otp, 'register_email' => $request->email]);

        // Send OTP Email
        try {
            Mail::send([], [], function ($message) use ($request, $otp) {
                $message->to($request->email)
                    ->subject('Your SSC Account Verification Code')
                    ->html(view('auth.emails.otp', ['otp' => $otp])->render());
            });
            $msg = 'Verification code sent! Please check your Microsoft school email inbox (or spam folder) for the 6-digit code.';
        } catch (\Exception $e) {
            Log::error('OTP email failed to send', ['error' => $e->getMessage()]);
            // Fallback for local testing if SMTP is not configured
            $msg = 'Verification initiated! (For local testing/preview: your code is ' . $otp . ')';
        }

        return response()->json([
            'success' => true,
            'message' => $msg,
        ]);
    }

    public function verifyOtp(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'otp'   => 'required|string|size:6',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'The code must be exactly 6 characters.',
            ]);
        }

        $sessionOtp = session('register_otp');
        $sessionEmail = session('register_email');

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
            $ua       = request()->userAgent() ?? '';
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
                'admin'     => redirect()->route('admin.dashboard'),
                'treasurer' => redirect()->route('treasurer.dashboard'),
                'officer'   => redirect()->route('officer.dashboard'),
                'dean'      => redirect()->route('dean.dashboard'),
                default     => redirect('/'),
            };
        }

        return $redirect->with('success', 'Welcome back, ' . $user->fullname . '! You have successfully signed into the portal.');
    }

    /**
     * Build a unique throttle key per email + IP address combination.
     */
    private function throttleKey(Request $request): string
    {
        return 'login|' . Str::lower($request->input('email', '')) . '|' . $request->ip();
    }
}
