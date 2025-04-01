<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;

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

        $user->sendEmailVerificationNotification();

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

        if (! $user) {
            return ['error' => 'Invalid email', 'status' => 401];
        }

        if (! Hash::check($credentials['password'], $user->password)) {
            return ['error' => 'Invalid password', 'status' => 401];
        }

        if (! $user->hasVerifiedEmail()) {
            return ['error' => 'Please verify your email before logging in.', 'status' => 403];
        }

        if (! $token = Auth::attempt($credentials)) {
            return ['error' => 'Error generating token', 'status' => 401];
        }

        if ($user->two_factor_enabled) {
            $otp = Str::random(6, '0123456789');
            $user->forceFill([
                'two_factor_otp' => $otp,
                'two_factor_expires_at' => Carbon::now()->addMinutes(5)
            ])->save();

            return [
                'message' => 'OTP required for 2FA authentication.',
                'otp_length' => 6,
                'expires_in' => 300, // 5 minutes
                'status' => 202
            ];
        }

        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60,
            'status' => 200
        ];
    }

    /**
     * Logout the user.
     */
    public function logout()
    {
        Auth::logout();
        return ['message' => 'Successfully logged out', 'status' => 200];
    }
}
