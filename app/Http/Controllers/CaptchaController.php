<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CaptchaController extends Controller
{
    /**
     * Generate a stateless signed CAPTCHA token.
     *
     * The token is: base64( timestamp + "|" + HMAC-SHA256(timestamp, APP_KEY) )
     * This requires no session — it can be verified on any serverless instance.
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
     * Verify a stateless CAPTCHA token.
     * Returns true if the token signature is valid and not older than 10 minutes.
     */
    public static function verifyToken(?string $token): bool
    {
        if (! $token) {
            return false;
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
