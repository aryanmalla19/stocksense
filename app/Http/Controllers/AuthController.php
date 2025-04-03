<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Info(
 *     title="StockSense API",
 *     version="1.0.0",
 *     description="API for managing stocks, sectors, and user authentication",
 *     @OA\Contact(
 *         email="support@example.com"
 *     )
 * )
 * @OA\Server(
 *     url="http://localhost:8080/api",
 *     description="Local Development Server"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="JWT",
 *     type="apiKey",
 *     name="Authorization",
 *     in="header",
 *     description="Enter token in format 'Bearer {token}'"
 * )
 */
class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @OA\Post(
     *     path="/v1/auth/register",
     *     tags={"Authentication"},
     *     summary="Register a new user",
     *     operationId="registerUser",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "password_confirmation"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="subrace@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="Password@123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="Password@123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User registered successfully"),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Subresh"),
     *                 @OA\Property(property="email", type="string", example="subrace@example.com")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return response()->json(
            ['message' => $result['message'], 'user' => $result['user'] ?? null],
            $result['status']
        );
    }

    /**
     * @OA\Post(
     *     path="/v1/auth/login",
     *     tags={"Authentication"},
     *     summary="Login a user",
     *     operationId="loginUser",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="subrace@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="Password@123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User logged in successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Login successful"),
     *             @OA\Property(property="token", type="string", example="Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid credentials")
     *         )
     *     )
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        return response()->json(
            array_filter($result, fn ($key) => $key !== 'status', ARRAY_FILTER_USE_KEY),
            $result['status']
        );
    }

    /**
     * @OA\Post(
     *     path="/v1/auth/logout",
     *     tags={"Authentication"},
     *     summary="Logout a user",
     *     operationId="logoutUser",
     *     security={{"JWt": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="User logged out successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully logged out")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function logout(): JsonResponse
    {
        $result = $this->authService->logout();
        return response()->json(['message' => $result['message']], $result['status']);
    }
}