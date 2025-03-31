<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\JsonResponse;

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


        if (!$user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Please verify your email before logging in.'], 403);
        }

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Error generating token'], 401);
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
}
