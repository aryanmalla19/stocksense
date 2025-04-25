<?php

namespace App\Http\Controllers;

use App\Events\UserRegistered;
use App\Models\User;
use Carbon\Carbon;
use Google_Client;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;

class SocialiteController extends Controller
{
    public function handleGoogleCallback(\Illuminate\Http\Request $request): JsonResponse
    {
        try {
            $idToken = $request->input('id_token');

            if (! $idToken) {
                return response()->json(['error' => 'ID token is required'], 400);
            }

            $client = new Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]);
            $payload = $client->verifyIdToken($idToken);

            if (! $payload) {
                return response()->json(['error' => 'Invalid ID token'], 401);
            }

            $googleId = $payload['sub']; // Google user ID
            $email = $payload['email'];
            $name = $payload['name'];

            $user = User::where('google_id', $googleId)->first();

            if (! $user) {
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'google_id' => $googleId,
                ]);

                event(new UserRegistered($user));
            }

            $token = JWTAuth::fromUser($user);
            $refreshToken = \Illuminate\Support\Str::random(64);

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
        } catch (\Exception $e) {
            \Log::error('Google OAuth failed: '.$e->getMessage());

            return response()->json([
                'error' => 'Authentication failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
