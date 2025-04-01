<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return response()->json(
            ['message' => $result['message'], 'user' => $result['user'] ?? null],
            $result['status']
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        return response()->json(
            array_filter($result, fn ($key) => $key !== 'status', ARRAY_FILTER_USE_KEY),
            $result['status']
        );
    }

    public function logout(): JsonResponse
    {
        $result = $this->authService->logout();
        return response()->json(['message' => $result['message']], $result['status']);
    }
}
