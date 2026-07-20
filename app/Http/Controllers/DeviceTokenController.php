<?php

namespace App\Http\Controllers;

use App\Models\DeviceToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeviceTokenController extends Controller
{
    /**
     * Store a new device token for the authenticated user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
            'device_type' => 'nullable|string|in:android,ios,web',
            'device_name' => 'nullable|string',
        ]);

        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Check if token already exists for this user
        $existingToken = DeviceToken::where('fcm_token', $request->fcm_token)
            ->where('user_id', $user->id)
            ->first();

        if ($existingToken) {
            // Update the existing token
            $existingToken->update([
                'device_type' => $request->device_type,
                'device_name' => $request->device_name,
                'is_active' => true,
            ]);
            return response()->json(['message' => 'Device token updated', 'data' => $existingToken], 200);
        }

        // Check if this token exists for another user and deactivate it
        DeviceToken::where('fcm_token', $request->fcm_token)->update(['is_active' => false]);

        // Create new device token
        $deviceToken = DeviceToken::create([
            'user_id' => $user->id,
            'fcm_token' => $request->fcm_token,
            'device_type' => $request->device_type,
            'device_name' => $request->device_name,
            'is_active' => true,
        ]);

        return response()->json(['message' => 'Device token registered', 'data' => $deviceToken], 201);
    }

    /**
     * Deactivate a device token.
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        DeviceToken::where('fcm_token', $request->fcm_token)
            ->where('user_id', $user->id)
            ->update(['is_active' => false]);

        return response()->json(['message' => 'Device token deactivated'], 200);
    }

    /**
     * Get all active device tokens for the authenticated user.
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $tokens = DeviceToken::where('user_id', $user->id)
            ->where('is_active', true)
            ->get();

        return response()->json(['data' => $tokens], 200);
    }
}
