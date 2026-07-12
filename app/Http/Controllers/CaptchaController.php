<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CaptchaController extends Controller
{
    public function verifyCaptcha(Request $request)
    {
        // Generate dynamic captcha token and store in session
        $token = Str::random(32);
        session(['captcha_token' => $token]);

        return response()->json([
            'success' => true,
            'token' => $token
        ]);
    }
}
