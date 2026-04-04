<?php

namespace App\Http\Controllers\V1;

use App\Events\AuthLoggedIn;
use App\Events\AuthLoggedOut;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    /**
     * Get a JWT via given credentials.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!($token = auth()->attempt($request->only('email', 'password')))) {
            return response()->json(['error' => __('auth.failed')], 401);
        }

        $user = auth()->user();

        $sessionId = auth()->payload()->get('session_id');

        event(new AuthLoggedIn($user, $sessionId, $request->ip(), $request->userAgent()));

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     */
    public function me(): JsonResponse
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     */
    public function logout(Request $request): JsonResponse
    {
        $sessionId = auth()->parseToken()->payload()->get('session_id');

        event(new AuthLoggedOut(auth()->user(), $sessionId, $request->ip(), $request->userAgent()));

        auth()->logout();

        return response()->json(['message' => __('auth.logout.success')]);
    }

    /**
     * Refresh a token.
     */
    public function refresh(): JsonResponse
    {
        try {
            return $this->respondWithToken(auth()->refresh());
        } catch (JWTException $e) {
            return response()->json(['error' => __('auth.token.cannot_refresh')], 401);
        }
    }

    /**
     * Get the token array structure.
     */
    protected function respondWithToken(string $token): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
        ]);
    }
}
