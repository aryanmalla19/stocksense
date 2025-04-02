<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;

class TwoFactorController extends Controller
{
    public function enable(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $user->update(['two_factor_enabled' => true]);

        return response()->json(['message' => '2FA enabled successfully'], 200);
    }

    public function disable(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $user->update(['two_factor_enabled' => false]);

        return response()->json(['message' => '2FA disabled successfully'], 200);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
            'email' => 'required|email',
            'private_token' => 'required|string|size:32'
        ]);

        // Find user by authentication or private_token
        $user = Auth::user() ?? User::where([
            'two_factor_secret' => $request->private_token,
            'email' => $request->email
        ])->first();

        if (!$user) {
            return response()->json(['error' => 'User not found or invalid token'], 401);
        }
        // Ensure OTP has not expired
        if (!$user->two_factor_expires_at || Carbon::parse($user->two_factor_expires_at)->lt(Carbon::now())) {
            return response()->json(['error' => 'OTP expired'], 401);
        }

        if ($request->otp !== $user->two_factor_otp) {
            return response()->json(['error' => 'Invalid OTP'], 401);
        }

        // Clear 2FA fields after successful verification
        $user->update([
            'two_factor_otp' => null,
            'two_factor_secret' => null,
            'two_factor_expires_at' => null
        ]);

        // Generate new JWT token
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ]);
    }
}
