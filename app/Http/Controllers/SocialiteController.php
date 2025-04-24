<?php

namespace App\Http\Controllers;

use App\Events\UserRegistered;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Facades\JWTAuth;

class SocialiteController extends Controller
{
    /**
     * Handle Google OAuth callback with access_token from frontend
     */
    public function handleGoogleCallback(Request $request): JsonResponse
    {
        try {
            $accessToken = $request->input('access_token');

            if (!$accessToken) {
                return response()->json(['error' => 'Access token is required'], 400);
            }

            $googleUser = Socialite::driver('google')->stateless()->userFromToken($accessToken);

            if (!$googleUser) {
                return response()->json(['error' => 'Unable to fetch Google user'], 401);
            }

            $user = User::where('google_id', $googleUser->id)->first();

            if (!$user) {
                // Register the user
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                ]);

                event(new UserRegistered($user));
            }

            $token = JWTAuth::fromUser($user);
            $refreshToken = Str::random(64);

            $user->forceFill([
                'refresh_token' => $refreshToken,
                'refresh_token_expires_at' => Carbon::now()->addDays(30),
            ])->save();

            return response()->json([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60,
                'refresh_token' => $refreshToken,
                'status' => 200,
            ]);
        } catch (Exception $e) {
            \Log::error('Google OAuth failed: ' . $e->getMessage());

            return response()->json([
                'error' => 'Authentication failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
