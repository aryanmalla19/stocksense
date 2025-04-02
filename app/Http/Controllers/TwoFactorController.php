<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
class TwoFactorController extends Controller
{
    public function enable(Request $request)
    {
        $request->validate([
            'password' => 'required'
        ]);

        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Token not provided'], 400);
        }

        $user = JWTAuth::parseToken()->authenticate();

        if ($user->two_factor_enabled) {
            return "Already enabled";
        }

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid password'], 400);
        }

        $user->forceFill([
            'two_factor_enabled' => true
        ])->save();

        return response()->json(['message' => '2FA enabled successfully'], 200);
    }
    public function  disable(Request $request){
        $request->validate([
            'password' => 'required'
        ]);

        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Token not provided'], 400);
        }

        $user = JWTAuth::parseToken()->authenticate();

        if (!$user->two_factor_enabled) {
            return "Already disabled";
        }

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid password'], 400);
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

        $user = Auth::user() ?? User::where('two_factor_secret', $request->private_token)->first();

        if (!$user) {
            return response()->json(['error' => 'Invalid token', 'status' => 401], 401);
        }

        if ($user->two_factor_expires_at < now()) {
            return response()->json(['error' => 'OTP expired', 'status' => 401], 401);
        }

        if ($user->two_factor_otp !== $request->otp) {
            return response()->json(['error' => 'Invalid OTP', 'status' => 401], 401);
        }

        // Clear 2FA fields after successful verification
        $user->forceFill([
            'two_factor_otp' => null,
            'two_factor_secret' => null,
            'two_factor_expires_at' => null
        ])->save();

        $token = Auth::login($user);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60,
        ]);
    }
}
