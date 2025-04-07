<?php

namespace App\Http\Controllers;

use App\Http\Requests\TwoFA\VerifyOtpRequest;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTFactory;

class TwoFactorController extends Controller
{
    public function enable()
    {
        $user = Auth::user();

        $user->update(['two_factor_enabled' => true]);
        return response()->json(['message' => '2FA enabled successfully']);
    }

    public function disable()
    {
        $user = Auth::user();

        $user->update(['two_factor_enabled' => false]);

        return response()->json(['message' => '2FA disabled successfully']);
    }

    public function verifyOtp(VerifyOtpRequest $request)
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

    $refreshToken = Str::random(32);
    $user->forceFill([
        'refresh_token' => $refreshToken,
        'refresh_token_expires_at' => Carbon::now()->addDays(30),
    ])->save();

    return response()->json([
        'access_token' => $token,
        'refresh_token' => $refreshToken,
        'token_type' => 'bearer',
        'expires_in' => JWTAuth::factory()->getTTL() * 60,
    ]);                                   
    }
}





