<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
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

        return response()->json(['message' => '2FA disabled successfully'], 200);
    }
}
