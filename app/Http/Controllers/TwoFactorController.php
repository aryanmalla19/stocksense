<?php

namespace App\Http\Controllers;

use App\Http\Requests\VerifyOtpRequest;
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

    public function verifyOtp(VerifyOtpRequest $request)
    {  
    $data = $request->validated();

    $user = Auth::user() ?? User::where([
        'two_factor_secret' => $data['private_token'],
        'email' => $data['email']
    ])->first();

    if (!$user) {
        return response()->json(['error' => 'User not found or invalid token'], 401);
    }

    if (!$user->two_factor_expires_at || Carbon::parse($user->two_factor_expires_at)->lt(now())) {
        return response()->json(['error' => 'OTP expired'], 401);
    }

    if ($data['otp'] !== $user->two_factor_otp) {
        return response()->json(['error' => 'Invalid OTP'], 401);
    }

    $user->update([
        'two_factor_otp' => null,
        'two_factor_secret' => null,
        'two_factor_expires_at' => null
    ]);

    $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);

    return response()->json([
        'access_token' => $token,
        'token_type' => 'bearer',
        'expires_in' => JWTAuth::factory()->getTTL() * 60,
    ]);

}
}





