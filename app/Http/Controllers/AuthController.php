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

        Auth::login($user);
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
            'password.regex' => 'Password must contain at least one letter and one number.',
            'password.min'   => 'Password must be at least 8 characters.',
        ]);

        if (! CaptchaController::verifyToken($request->input('captcha_verified_token'))) {
            return back()->withErrors(['email' => 'Security check failed. Please verify that you are not a robot.'])->withInput();
        }

        // ── Verify with Microsoft if the school email account actively exists ──────
        try {
            $msResponse = Http::timeout(6)
                ->post('https://login.microsoftonline.com/common/GetCredentialType', [
                    'Username' => $request->email
                ]);

            if ($msResponse->successful()) {
                $ifExistsResult = $msResponse->json('IfExistsResult');
                // IfExistsResult of 1 explicitly indicates that the user account does NOT exist on MS servers.
                if ($ifExistsResult === 1) {
                    return back()->withErrors([
                        'email' => 'This Microsoft 365 account does not exist. Please double-check your school email address spelling or contact the school IT administrator.'
                    ])->withInput();
                }
            }
        } catch (\Exception $e) {
            // Log warning but proceed with registration so network/DNS hiccups on Microsoft's side do not lock registrations
            Log::warning('Failed to query Microsoft realm user verification', ['error' => $e->getMessage()]);
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

        SscHelper::logActivity($user->id, 'REGISTER', "Student registered: {$user->email}");

        // Generate temporary signed URL valid for 24 hours
        $confirmUrl = URL::temporarySignedRoute(
            'confirm-account', 
            now()->addHours(24), 
            ['user' => $user->id]
        );

        // Send Confirmation Email
        try {
            Mail::send([], [], function ($message) use ($user, $confirmUrl) {
                $message->to($user->email)
                    ->subject('Confirm your SSC Transparency Account')
                    ->html(view('auth.emails.confirm', ['user' => $user, 'url' => $confirmUrl])->render());
            });
            $successMsg = 'Registration successful! A confirmation link has been sent to your school email (' . $user->email . '). Please check your inbox (or spam/junk folder) and confirm your account to sign in.';
        } catch (\Exception $e) {
            Log::error('Registration email failed to send', ['error' => $e->getMessage()]);
            // Graceful fallback if SMTP isn't configured so the student isn't blocked
            $successMsg = 'Registration successful! However, we could not send a confirmation email at this moment. Please ask an administrator to activate your account.';
        }

        return redirect()->route('login', ['portal' => 'student'])
            ->with('success', $successMsg);
    }

    public function confirmAccount(Request $request, User $user)
    {
        if (! $request->hasValidSignature()) {
            abort(401, 'This confirmation link is invalid or has expired.');
        }

        if ($user->status === 'active') {
            return redirect()->route('login', ['portal' => 'student'])
                ->with('success', 'Your account is already active. Please sign in.');
        }

        $user->update(['status' => 'active']);
        
        SscHelper::logActivity($user->id, 'ACTIVATE_EMAIL', "Student account verified & activated via email: {$user->email}");

        return redirect()->route('login', ['portal' => 'student'])
            ->with('success', 'Email confirmed successfully! Your account has been activated. You can now log in.');
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

        return response()->json([
            'success' => true,
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
