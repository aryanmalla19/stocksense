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

        $user->forceFill([
            'two_factor_enabled' => true
        ])->save();

        return response()->json(['message' => '2FA enabled successfully'], 200);
    }

    public function disable(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $user->forceFill([
            'two_factor_enabled' => false
        ])->save();

        return response()->json(['message' => '2FA disabled successfully']);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
            'private_token' => 'required|string|size:32'
        ]);

        // Authenticate user first before verifying OTP
        $user = Auth::user() ?? User::where('two_factor_secret', $request->private_token)->first();

        if (!$user) {
            return response()->json(['error' => 'Invalid token'], 401);
        }

        // Ensure OTP has not expired
        if (!$user->two_factor_expires_at || $user->two_factor_expires_at->lt(Carbon::now())) {
            return response()->json(['error' => 'OTP expired'], 401);
        }

        if ($request->otp !== $user->two_factor_otp) {
            return response()->json(['error' => 'Invalid OTP'], 401);
        }

        // Clear 2FA fields after successful verification
        $user->forceFill([
            'two_factor_otp' => null,
            'two_factor_secret' => null,
            'two_factor_expires_at' => null
        ])->save();

        // Generate new JWT token
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60,
        ]);
    }
}
