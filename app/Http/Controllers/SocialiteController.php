<?php

namespace App\Http\Controllers;

use App\Events\UserRegistered;
use App\Models\User;
use Carbon\Carbon;
use Google_Client;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="OAuth and authentication related endpoints"
 * )
 */

 /**
 * @OA\Schema(
 *     schema="GoogleOAuthCallbackResponse",
 *     title="Google OAuth Callback Response",
 *     description="Request and Response structure for Google OAuth callback",
 *     type="object",
 *     required={"id_token"},
 *     @OA\Property(property="id_token", type="string", description="The Google ID token"),
 *     @OA\Property(property="access_token", type="string", example="your-access-token", description="JWT Access Token returned after authentication"),
 *     @OA\Property(property="token_type", type="string", example="bearer", description="The type of token returned"),
 *     @OA\Property(property="expires_in", type="integer", example=3600, description="The expiration time of the access token in seconds"),
 *     @OA\Property(property="refresh_token", type="string", example="your-refresh-token", description="JWT Refresh Token returned after authentication"),
 *     @OA\Property(property="status", type="integer", example=200, description="HTTP status code of the response"),
 *     @OA\Property(property="error", type="string", example="Invalid ID token", description="Error message for invalid ID token"),
 *     @OA\Property(property="message", type="string", example="Detailed error message", description="Additional error details")
 * )
 */
class SocialiteController extends Controller
{
     /**
     * @OA\Post(
     *     path="/api/auth/google/callback",
     *     summary="Handle Google OAuth callback",
     *     description="Handles the Google OAuth callback, verifies the ID token, and returns an access token and refresh token",
     *     operationId="handleGoogleCallback",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Google ID token",
     *         @OA\JsonContent(
     *             required={"id_token"},
     *             @OA\Property(property="id_token", type="string", description="The Google ID token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful authentication",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="access_token", type="string", example="your-access-token"),
     *             @OA\Property(property="token_type", type="string", example="bearer"),
     *             @OA\Property(property="expires_in", type="integer", example=3600),
     *             @OA\Property(property="refresh_token", type="string", example="your-refresh-token"),
     *             @OA\Property(property="status", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="ID token is required",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="ID token is required")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid ID token",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Invalid ID token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Authentication failed"),
     *             @OA\Property(property="message", type="string", example="Detailed error message")
     *         )
     *     )
     * )
     */
    public function handleGoogleCallback(\Illuminate\Http\Request $request): JsonResponse
    {
        try {
            $idToken = $request->input('id_token');

            if (! $idToken) {
                return response()->json(['error' => 'ID token is required'], 400);
            }

            $client = new Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]);
            $payload = $client->verifyIdToken($idToken);

            if (! $payload) {
                return response()->json(['error' => 'Invalid ID token'], 401);
            }

            $googleId = $payload['sub']; // Google user ID
            $email = $payload['email'];
            $name = $payload['name'];

            $user = User::where('google_id', $googleId)->first();

            if (! $user) {
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'google_id' => $googleId,
                ]);

                event(new UserRegistered($user));
            }

            $token = JWTAuth::fromUser($user);
            $refreshToken = \Illuminate\Support\Str::random(64);

            $user->forceFill([
                'refresh_token' => $refreshToken,
                'refresh_token_expires_at' => Carbon::now()->addDays(30),
            ])->save();

            return response()->json([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60,
                'refresh_token' => $refreshToken,
                'status' => 200,
            ]);
        } catch (\Exception $e) {
            \Log::error('Google OAuth failed: '.$e->getMessage());

            return response()->json([
                'error' => 'Authentication failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
