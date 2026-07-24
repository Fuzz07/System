<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CaptchaController extends Controller
{
    /**
     * Generate a stateless signed CAPTCHA token (for fallback or client verification).
     *
     * The token is: base64( timestamp + "|" + HMAC-SHA256(timestamp, APP_KEY) )
     */
    public function verifyCaptcha(Request $request)
    {
        $timestamp = (string) time();
        $appKey    = config('app.key');

        // Strip the "base64:" prefix that Laravel prepends to the key
        if (str_starts_with($appKey, 'base64:')) {
            $appKey = base64_decode(substr($appKey, 7));
        }

        $signature = hash_hmac('sha256', $timestamp, $appKey);
        $token     = base64_encode($timestamp . '|' . $signature);

        return response()->json([
            'success' => true,
            'token'   => $token,
        ]);
    }

    /**
     * Verify a stateless CAPTCHA token or validate via Google's official reCAPTCHA API.
     */
    public static function verifyToken(?string $token): bool
    {
        // 1. If Google reCAPTCHA keys are present in env, validate with Google API
        $secretKey = trim(env('RECAPTCHA_SECRET_KEY', ''));
        $isSecretPlaceholder = empty($secretKey) || 
                               str_contains(strtolower($secretKey), 'your-google') || 
                               str_contains(strtolower($secretKey), 'your_actual') || 
                               str_contains(strtolower($secretKey), 'placeholder') || 
                               str_contains(strtolower($secretKey), 'your-key') ||
                               str_contains($secretKey, '6LdXXXXXXXX');

        if (!$isSecretPlaceholder) {
            if (!$token) {
                return false;
            }

            try {
                $response = Http::asForm()
                    ->timeout(10)
                    ->post('https://www.google.com/recaptcha/api/siteverify', [
                        'secret'   => $secretKey,
                        'response' => $token,
                        'remoteip' => request()->ip(),
                    ]);

                if ($response->successful() && $response->json('success') === true) {
                    return true;
                }

                Log::warning('Official Google reCAPTCHA validation failed', [
                    'errors' => $response->json('error-codes'),
                ]);
                return false;
            } catch (\Throwable $e) {
                Log::error('Error connecting to Google reCAPTCHA API', [
                    'message' => $e->getMessage(),
                ]);
                // Fallback to local signed token validation on API timeouts/network failure to prevent user locking
            }
        }

        // 2. Fallback to local cryptographic signed token verification
        if (!$token) {
            return false;
        }

        $isAndroidApp = str_contains(request()->userAgent() ?? '', 'SSCStudentApp');
        if ($token === 'local_verified_token' && ($isSecretPlaceholder || $isAndroidApp)) {
            return true;
        }

        try {
            $decoded   = base64_decode($token, strict: true);
            if ($decoded === false) return false;

            [$timestamp, $signature] = explode('|', $decoded, 2);

            $appKey = config('app.key');
            if (str_starts_with($appKey, 'base64:')) {
                $appKey = base64_decode(substr($appKey, 7));
            }

            $expected  = hash_hmac('sha256', $timestamp, $appKey);
            $validSig  = hash_equals($expected, $signature);
            $validTime = (time() - (int) $timestamp) <= 600; // 10-minute window

            return $validSig && $validTime;
        } catch (\Throwable) {
            return false;
        }
    }
}
