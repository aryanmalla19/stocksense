<?php

namespace App\Services;

use App\Events\UserRegistered;
use App\Mail\OtpVerification;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    /**
     * Register a new user.
     */
    public function register(array $data)
    {
        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = Hash::make($data['password']);

        if (! $user->save()) {
            return ['error' => 'Error registering user', 'status' => 500];
        }
        event(new UserRegistered($user));

        return [
            'message' => 'User registered successfully. Check email for verification',
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
            'status' => 201
        ];
    }

    /**
     * Authenticate user login.
     */
    public function login(array $credentials)
    {
        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            return ['error' => 'Invalid email', 'status' => 401];
        }

        if (!Hash::check($credentials['password'], $user->password)) {
            return ['error' => 'Invalid password', 'status' => 401];
        }

        if (!$user->hasVerifiedEmail()) {
            return ['error' => 'Please verify your email before logging in.', 'status' => 403];
        }

        if ($user->two_factor_enabled) {
            $otp = Str::random(6, '0123456789');
            $privateToken = Str::random(32); // Secure random token for 2FA

            $user->forceFill([
                'two_factor_otp' => $otp,
                'two_factor_secret' => $privateToken,
                'two_factor_expires_at' => Carbon::now()->addMinutes(5),
            ])->save();

            Mail::to($user->email)->queue(new OtpVerification($user, $otp));

            return [
                'message' => 'OTP required for 2FA authentication.',
                'private_token' => $privateToken,
                'otp_length' => 6,
                'expires_in' => 300, // 5 minutes
                'status' => 202,
            ];
        }

        if (!$token = JWTAuth::attempt($credentials)) {
            return ['error' => 'Invalid credentials', 'status' => 401];
        }

        // Generate a random alphanumeric refresh token
        $refreshToken = Str::random(32);

        $user->forceFill([
            'refresh_token' => $refreshToken,
            'refresh_token_expires_at' => Carbon::now()->addDays(30), // Server-side expiration
        ])->save();

        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'refresh_token' => $refreshToken,
            'status' => 200,
        ];
    }
}
