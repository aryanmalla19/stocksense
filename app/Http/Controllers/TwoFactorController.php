<?php

namespace App\Http\Controllers;

use App\Http\Requests\TwoFA\VerifyOtpRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="Operations related to user authentication"
 * )
 */

/**
 * @OA\Schema(
 *     schema="VerifyOtpRequest",
 *     type="object",
 *     required={"otp", "email", "private_token"},
 *     @OA\Property(
 *         property="otp",
 *         type="string",
 *         description="The One-Time Password (OTP) sent to user's email or phone.",
 *         example="123456",
 *         minLength=6,
 *         maxLength=6
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         description="The user's email address.",
 *         example="user@example.com"
 *     ),
 *     @OA\Property(
 *         property="private_token",
 *         type="string",
 *         description="A 32-character secret token associated with the user session.",
 *         example="ab12cd34ef56gh78ij90kl12mn34op56",
 *         minLength=32,
 *         maxLength=32
 *     )
 * )
 */
class TwoFactorController extends Controller
{
     /**
     * @OA\Post(
     *     path="/api/v1/auth/2fa/enable",
     *     summary="Enable Two-Factor Authentication",
     *     description="Enables 2FA for the authenticated user.",
     *     tags={"Two Factor Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="2FA enabled successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="2FA enabled successfully")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function enable()
    {
        $user = Auth::user();

        $user->update(['two_factor_enabled' => true]);

        return response()->json(['message' => '2FA enabled successfully']);
    }

     /**
     * @OA\Post(
     *     path="/api/v1/auth/2fa/disable",
     *     summary="Disable Two-Factor Authentication",
     *     description="Disables 2FA for the authenticated user.",
     *     tags={"Two Factor Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="2FA disabled successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="2FA disabled successfully")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function disable()
    {
        $user = Auth::user();

        $user->update([
            'two_factor_enabled' => false,
            'two_factor_otp' => null,
            'two_factor_secret' => null,
            'two_factor_expires_at' => null,

        ]);

        return response()->json(['message' => '2FA disabled successfully']);
    }

     /**
     * @OA\Post(
     *     path="/api/v1/auth/verify-otp",
     *     summary="Verify OTP for Two-Factor Authentication",
     *     description="Verifies the user's One-Time Password (OTP) during the 2FA process. Returns new JWT tokens if successful.",
     *     tags={"Two Factor Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/VerifyOtpRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP verified successfully, access and refresh tokens returned.",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string", example="eyJhbGciOi..."),
     *             @OA\Property(property="refresh_token", type="string", example="abc123abc123abc123abc123abc123ab"),
     *             @OA\Property(property="token_type", type="string", example="bearer"),
     *             @OA\Property(property="expires_in", type="integer", example=3600)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid OTP, expired OTP, or user not found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Invalid OTP")
     *         )
     *     )
     * )
     */
    public function verifyOtp(VerifyOtpRequest $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
            'email' => 'required|email',
            'private_token' => 'required|string|size:32',
        ]);

        // Find user by authentication or private_token
        $user = Auth::user() ?? User::where([
            'two_factor_secret' => $request->private_token,
            'email' => $request->email,
        ])->first();

        if (! $user) {
            return response()->json(['error' => 'User not found or invalid token'], 401);
        }
        // Ensure OTP has not expired
        if (! $user->two_factor_expires_at || Carbon::parse($user->two_factor_expires_at)->lt(Carbon::now())) {
            return response()->json(['error' => 'OTP expired'], 401);
        }

        if ($request->otp !== $user->two_factor_otp) {
            return response()->json(['error' => 'Invalid OTP'], 401);
        }

        // Clear 2FA fields after successful verification
        $user->update([
            'two_factor_otp' => null,
            'two_factor_secret' => null,
            'two_factor_expires_at' => null,
        ]);

        // Generate new JWT token
        $token = JWTAuth::fromUser($user);

        $refreshToken = Str::random(32);
        $user->forceFill([
            'refresh_token' => $refreshToken,
            'refresh_token_expires_at' => Carbon::now()->addDays(30),
        ])->save();

        return response()->json([
            'access_token' => $token,
            'refresh_token' => $refreshToken,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ]);
    }
}
