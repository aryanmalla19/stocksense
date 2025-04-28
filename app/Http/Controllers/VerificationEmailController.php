<?php

namespace App\Http\Controllers;

use App\Mail\UserVerification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

/**
 * @OA\Tag(
 *     name="Email Verification",
 *     description="Operations related to user verification"
 * )
 */
class VerificationEmailController extends Controller
{
    /**
     * @OA\Get(
     *     path="/v1/auth/email/verify/{id}/{hash}",
     *     summary="Verify user's email address",
     *     description="Verify a user's email when they click the verification link sent to them.",
     *     tags={"Authentication"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the user",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="hash",
     *         in="path",
     *         required=true,
     *         description="SHA1 hash of the user's email",
     *         @OA\Schema(type="string", example="5d41402abc4b2a76b9719d911017c592")
     *     ),
     *     @OA\Response(
     *         response=302,
     *         description="Redirects to frontend URL with query parameters (success or error)"
     *     )
     * )
     */
    public function verify(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        // Verify hash
        if (! hash_equals((string) $hash, sha1($user->email))) {
            $redirectUrl = config('app.frontend_url').'/email-verified?error=invalid_link';

            return redirect()->to($redirectUrl);
        }

        // Already verified
        if ($user->hasVerifiedEmail()) {
            $redirectUrl = config('app.frontend_url').'/email-verified?message=already_verified';

            return redirect()->to($redirectUrl);
        }

        // Mark as verified
        $user->markEmailAsVerified();

        // Create access token
        $accessToken = \JWTAuth::fromUser($user);

        // Generate refresh token
        $refreshToken = \Illuminate\Support\Str::random(32);

        // Save refresh token to DB
        $user->forceFill([
            'refresh_token' => $refreshToken,
            'refresh_token_expires_at' => Carbon::now()->addDays(30),
        ])->save();

        // Redirect to frontend with tokens
        $redirectUrl = config('app.frontend_url').'/email-verified?'.http_build_query([
            'message' => 'email_verified',
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            //                'expires_in' => config('jwt.ttl') * 60,
        ]);

        return redirect()->to($redirectUrl);
    }

    /**
     * @OA\Post(
     *     path="/v1/auth/email/resend",
     *     summary="Resend verification email",
     *     description="Resend a verification email to the user if not already verified.",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Verification email resent",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Verification email resent.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="User not found or already verified",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User not found or already verified.")
     *         )
     *     )
     * )
     */
    public function resend(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();

        if ($user && ! $user->hasVerifiedEmail()) {
            Mail::to($user->email)->queue(new UserVerification($user));

            return response()->json(['message' => 'Verification email resent.']);
        }

        return response()->json(['message' => 'User not found or already verified.'], 400);
    }
}
