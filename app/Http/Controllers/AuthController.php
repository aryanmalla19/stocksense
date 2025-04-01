<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRegisterRequest;
use App\Http\Requests\UserLoginRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\JsonResponse;

class AuthController extends Controller
{
    public function register(UserRegisterRequest $request): JsonResponse
    {
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

    public function login(UserLoginRequest $request): JsonResponse
    {
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

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     */
    public function me(): JsonResponse
    {
        return response()->json(auth('api')->user());
    }

    /**
     * Log the user out (Invalidate the token).
     */
    public function logout(): JsonResponse
    {
        auth('api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     */
    public function refresh(): JsonResponse
    {
        return $this->respondWithToken(auth('api')->refresh());
    }

    /**
     * Get the token array structure.
     */
    protected function respondWithToken($token): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ]);
    }
}