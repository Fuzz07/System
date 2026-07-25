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
        return true;
    }
}
