<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;

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
 *     url="http://localhost:8000/api/v1",
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
class SocialiteController extends Controller
{
    /**
     * @OA\Get(
     *     path="/auth/google",
     *     tags={"Authentication"},
     *     summary="Redirect to Google for OAuth login",
     *     description="Redirects the user to Google's OAuth login page. This endpoint should be opened in a browser and is not testable via Swagger UI.",
     *     operationId="googleLogin",
     *     @OA\Response(
     *         response=302,
     *         description="Redirect to Google OAuth"
     *     )
     * )
     */
    public function googleLogin(): \Illuminate\Http\RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * @OA\Get(
     *     path="/auth/google/callback",
     *     tags={"Authentication"},
     *     summary="Handle Google OAuth callback and return JWTs",
     *     description="Handles the callback from Google OAuth, logs in or creates the user, and returns access/refresh tokens.",
     *     operationId="googleCallback",
     *     @OA\Response(
     *         response=200,
     *         description="Successful authentication",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1Qi..."),
     *             @OA\Property(property="token_type", type="string", example="bearer"),
     *             @OA\Property(property="expires_in", type="integer", example=3600),
     *             @OA\Property(property="refresh_token", type="string", example="eyJhbGciOiJIUzI1..."),
     *             @OA\Property(property="status", type="integer", example=200),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Authentication failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unable to authenticate with Google")
     *         )
     *     )
     * )
     */
    public function googleAuthentication(): JsonResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            if (!$googleUser) {
                return response()->json(['error' => 'Google user does not exist'], 401);
            }

            $user = User::where('google_id', $googleUser->id)->first();

            if ($user) {
                $token = JWTAuth::fromUser($user);
                $refreshToken = $user->refresh_token;

                if (!$refreshToken) {
                    $refreshToken = $this->generateRefreshToken($user);
                    $user->update(['refresh_token' => $refreshToken]);
                }

                return response()->json([
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => config('jwt.ttl') * 60,
                    'refresh_token' => $refreshToken,
                    'status' => 200,
                ]);
            }

            $user = User::create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
            ]);

            $token = JWTAuth::fromUser($user);
            $refreshToken = $this->generateRefreshToken($user);
            $user->update(['refresh_token' => $refreshToken]);

            return response()->json([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60,
                'refresh_token' => $refreshToken,
                'status' => 200,
            ]);
        } catch (Exception $e) {
            \Log::error('Google OAuth error: ' . $e->getMessage());
            return response()->json(['error' => 'Authentication failed'], 500);
        }
    }

    protected function generateRefreshToken(User $user): string
    {
        $customClaims = JWTFactory::customClaims([
            'sub' => $user->id,
            'iat' => now()->timestamp,
            'exp' => now()->addDays(30)->timestamp,
        ])->make();

        return JWTAuth::encode($customClaims)->get();
    }
}