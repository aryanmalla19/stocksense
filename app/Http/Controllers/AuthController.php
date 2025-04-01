<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailVerifyOtp;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $request->validate(
            [
                'name' => 'required|string|max:50|min:5',
                'email' => 'required|email|unique:users',
                'password' => [
                    'required',
                    'min:8',
                    'max:50',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/',
                    'confirmed',
                ],
                'password_confirmation' => 'required_with:password',
            ],
            [
                'name.required' => 'Name is missing',
                'name.max' => 'Name must not exceed 50 characters',
                'name.min' => 'Name must be at least 5 characters',
                'email.required' => 'Email is missing',
                'email.email' => 'Invalid email format',
                'email.unique' => 'Email is already taken',
                'password.required' => 'Password is missing',
                'password.min' => 'Password must be at least 8 characters',
                'password.max' => 'Password must not exceed 50 characters',
                'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character',
                'password.confirmed' => 'Password confirmation does not match',
                'password_confirmation.required_with' => 'Password confirmation is required',
            ]
        );

        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);

        if (! $user->save()) {
            return response()->json([
                'message' => 'Error registering user',
            ], 500);
        }

        event(new Registered($user));

        return response()->json([
            'message' => 'User registered successfully. Check email for verification',
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
        ], 201);

    }

    public function login(Request $request)
    {
        $request->validate(
            [
                'email' => 'required | email',
                'password' => 'required',
            ],
            [
                'email.required' => 'Email missing',
                'email.email' => 'Not in email format',

                'password.required' => 'Password missing',
            ]
        );

        $credentials = $request->only(['email', 'password']);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user) {
            return response()->json([
                'message' => 'Invalid email',
            ], 401);
        }

        if (! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid password',
            ], 401);
        }

        if (! $user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Please verify your email before logging in.'], 403);
        }

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Error generating token'], 401);
        }
        if ($user->two_factor_enabled){
            $otp = rand(100000, 999999);

            $success = Mail::raw('Your OTP: ' . $otp, function ($message) use ($user) {
                $message->to($user['email'])
                    ->subject("OTP Email");
            });


            if(! $success) return response()->json(['Error sending OTP']);

            $user->forceFill([
                'two_factor_otp' => $otp,
                'two_factor_expires_at' => Carbon::now()->addMinutes(5)
            ])->save();

            return $this->respondWithToken($token);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth('api')->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth('api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string  $token
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ]);
    }

    public function verify(Request $request){
        $request->validate([
            'token' => 'required | string'
        ]);

        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Token not provided'], 400);
        }

        $user = JWTAuth::parseToken()->authenticate();

        if($user->two_factor_otp != $request->token){
            return response()->json([
                'message' => 'OTP not matched'
            ], 200);
        }

        if ($user->two_factor_expires_at && $user->two_factor_expires_at->isFuture()) {
            return response()->json([
                'message' => 'Successfully login'
            ], 200);
        } else {
            return response()->json([
                'message' => 'OTP expired'
            ], 200);
        }
    }
}
