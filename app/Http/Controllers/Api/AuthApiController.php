<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Helpers\SscHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthApiController extends Controller
{
    /**
     * Authenticate a user and return a JWT token.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
            'portal'   => 'nullable|string|in:student,officer,treasurer,admin,dean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $credentials = $request->only('email', 'password');
        $portal = $request->input('portal', 'student');

        $allowedRoles = match ($portal) {
            'admin'     => ['admin'],
            'treasurer' => ['treasurer'],
            'officer'   => ['officer', 'treasurer'],
            'dean'      => ['dean'],
            default     => ['student'],
        };

        // Attempt JWT authentication
        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password.',
            ], 401);
        }

        /** @var \App\Models\User $user */
        $user = auth('api')->user();

        // Verify user status
        if ($user->status !== 'active') {
            auth('api')->logout();
            return response()->json([
                'success' => false,
                'message' => 'Your account is not active. Please wait for admin approval.',
            ], 403);
        }

        // Verify role matches requested portal
        if (! in_array($user->role, $allowedRoles)) {
            auth('api')->logout();
            return response()->json([
                'success' => false,
                'message' => "Access denied. You do not have permission for the {$portal} portal.",
            ], 403);
        }

        // Check if student graduated and auto-deactivated
        if ($user->isStudent() && config('ssc.auto_deactivate_graduates', true) && $user->isGraduated()) {
            $user->update(['status' => 'inactive']);
            auth('api')->logout();
            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated due to graduation.',
            ], 403);
        }

        SscHelper::logActivity($user->id, 'API_LOGIN', "Logged in via API ({$portal})");

        return $this->respondWithToken($token, $user);
    }

    /**
     * Get the authenticated user's profile.
     */
    public function me()
    {
        /** @var \App\Models\User $user */
        $user = auth('api')->user();

        return response()->json([
            'success' => true,
            'data'    => [
                'id'          => $user->id,
                'student_id'  => $user->student_id,
                'fullname'    => $user->fullname,
                'first_name'  => $user->first_name,
                'last_name'   => $user->last_name,
                'email'       => $user->email,
                'role'        => $user->role,
                'department'  => $user->department,
                'year_level'  => $user->year_level,
                'position'    => $user->position,
                'status'      => $user->status,
                'photo_url'   => $user->photo_url,
                'avatar'      => $user->avatar,
            ],
        ]);
    }

    /**
     * Refresh the current JWT token.
     */
    public function refresh()
    {
        try {
            $newToken = auth('api')->refresh();
            return $this->respondWithToken($newToken, auth('api')->user());
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not refresh token: ' . $e->getMessage(),
            ], 401);
        }
    }

    /**
     * Invalidate token and log the user out.
     */
    public function logout()
    {
        $userId = auth('api')->id();
        auth('api')->logout();

        if ($userId) {
            SscHelper::logActivity($userId, 'API_LOGOUT', 'Logged out via API');
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out.',
        ]);
    }

    /**
     * Helper to structure token JSON response.
     */
    protected function respondWithToken(string $token, User $user)
    {
        return response()->json([
            'success'      => true,
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth('api')->factory()->getTTL() * 60,
            'user'         => [
                'id'         => $user->id,
                'student_id' => $user->student_id,
                'fullname'   => $user->fullname,
                'email'      => $user->email,
                'role'       => $user->role,
                'department' => $user->department,
                'year_level' => $user->year_level,
                'position'   => $user->position,
                'photo_url'  => $user->photo_url,
            ],
        ]);
    }
}
