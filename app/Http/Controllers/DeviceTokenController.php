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

        // A Firebase token uniquely identifies one app installation. Reassign the
        // existing row when another student signs in on the same device.
        $deviceToken = DeviceToken::updateOrCreate(
            ['fcm_token' => $request->fcm_token],
            [
            'user_id' => $user->id,
            'device_type' => $request->device_type,
            'device_name' => $request->device_name,
            'is_active' => true,
            'last_used_at' => now(),
            ]
        );

        return response()->json([
            'message' => $deviceToken->wasRecentlyCreated
                ? 'Device token registered'
                : 'Device token updated',
            'data' => $deviceToken,
        ], $deviceToken->wasRecentlyCreated ? 201 : 200);
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
