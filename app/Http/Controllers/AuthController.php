<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Services\AuthService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;

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
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 minLength=5,
     *                 maxLength=50,
     *                 example="John Doe",
     *                 description="User's full name (5-50 characters)"
     *             ),
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 format="email",
     *                 example="subrace@example.com",
     *                 description="Unique email address"
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 type="string",
     *                 format="password",
     *                 minLength=8,
     *                 maxLength=50,
     *                 example="Password@123",
     *                 description="Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&)"
     *             ),
     *             @OA\Property(
     *                 property="password_confirmation",
     *                 type="string",
     *                 format="password",
     *                 example="Password@123",
     *                 description="Must match the password field"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User registered successfully"),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
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
            [
                'message' => $result['message'],
                'user' => $result['user'] ?? null,
            ],
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
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 format="email",
     *                 example="subrace@example.com",
     *                 description="User's email address"
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 type="string",
     *                 format="password",
     *                 example="Password@123",
     *                 description="User's password"
     *             )
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
            array_filter(
                $result,
                fn($key) => $key !== 'status',
                ARRAY_FILTER_USE_KEY
            ),
            $result['status']
        );
    }

    /**
     * @OA\Post(
     *     path="/v1/auth/refresh",
     *     tags={"Authentication"},
     *     summary="Refresh an access token using a refresh token",
     *     operationId="refreshToken",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"refresh_token"},
     *             @OA\Property(
     *                 property="refresh_token",
     *                 type="string",
     *                 example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
     *                 description="The refresh token issued during login"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Token refreshed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
     *             @OA\Property(property="token_type", type="string", example="bearer"),
     *             @OA\Property(property="expires_in", type="integer", example=3600)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Refresh token missing",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Refresh token is required")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid or expired refresh token",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Invalid refresh token")
     *         )
     *     )
     * )
     */

    public function refresh(Request $request)
    {
        $data = $request->validate([
            'refresh_token' => 'required'
        ]);

        $user = User::where('refresh_token', $data['refresh_token'])->first();

        // Check if user exists and token is still valid
        if (!$user || Carbon::now()->greaterThan($user->refresh_token_expires_at)) {
            return response()->json(['error' => 'Invalid or expired refresh token'], 401);
        }

        // Generate a new access token
        $newAccessToken = JWTAuth::fromUser($user);

        // Generate a new refresh token
        $newRefreshToken = Str::random(32);

        // Update refresh token in the database
        $user->forceFill([
            'refresh_token' => $newRefreshToken,
            'refresh_token_expires_at' => Carbon::now()->addDays(30)->toDateTimeString(), // Ensure timestamp format
        ])->save();

        return response()->json([
            'access_token' => $newAccessToken,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60, // Access token TTL in seconds
            'refresh_token' => $newRefreshToken,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/v1/auth/logout",
     *     tags={"Authentication"},
     *     summary="Logout a user",
     *     operationId="logoutUser",
     *     security={{"JWT": {}}},
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
    public function logout()
    {

      $user = JWTAuth::user();

      // Invalidate the current access token
      JWTAuth::invalidate(JWTAuth::getToken());

      // Clear the refresh token from the database
      $user->forceFill(['refresh_token' => null, 'refresh_token_expires_at' => null])->save();

      return response()->json([
          'message' => 'Successfully logged out',
      ]);
    }
}
