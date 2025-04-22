<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;

class SocialiteController extends Controller
{
    public function googleLogin(): \Illuminate\Http\RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function googleAuthentication(): JsonResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            if (!$googleUser) {
                return response()->json(['error' => 'Google user does not exist'], 401);
            }

            $user = User::where('google_id', $googleUser->id)->first();

            if ($user) {
                $token = JWTAuth::fromUser($user);
                // Generate a random alphanumeric refresh token
                $refreshToken = Str::random(32);

                $user->forceFill([
                    'refresh_token' => $refreshToken,
                    'refresh_token_expires_at' => Carbon::now()->addDays(30), // Server-side expiration
                ])->save();

                return response()->json([
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => config('jwt.ttl') * 60,
                    'refresh_token' => $refreshToken,
                    'status' => 200,
                ]);
            }

            $user = User::create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
            ]);

            $token = JWTAuth::fromUser($user);
            // Generate a random alphanumeric refresh token
            $refreshToken = Str::random(32);

            $user->forceFill([
                'refresh_token' => $refreshToken,
                'refresh_token_expires_at' => Carbon::now()->addDays(30), // Server-side expiration
            ])->save();

            return response()->json([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60,
                'refresh_token' => $refreshToken,
                'status' => 200,
            ]);
        } catch (Exception $e) {
            \Log::error('Google OAuth error: ' . $e->getMessage());
            return response()->json(['error' => 'Authentication failed'], 500);
        }
    }
}
