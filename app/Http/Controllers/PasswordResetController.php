<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * @OA\Tag(
 *     name="Password Reset",
 *     description="Endpoints for password reset functionality"
 * )
 */
class PasswordResetController extends Controller
{
    /**
     * @OA\Post(
     *     path="/v1/auth/forgot-password",
     *     tags={"Password Reset"},
     *     summary="Send a password reset link to the user's email",
     *     operationId="sendResetPasswordLink",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 format="email",
     *                 example="user@example.com",
     *                 description="The user's email address"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reset link sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="A password reset link has been sent to your email."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Email not found",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="We cannot find a user with that email address."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="Too many requests",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Too Many Attempts."
     *             )
     *         )
     *     )
     * )
     */
    public function sendResetPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => __('A password reset link has been sent to your email.')], 200)
            : response()->json(['error' => __('We cannot find a user with that email address.')], 400);
    }

    /**
     * @OA\Post(
     *     path="/v1/auth/reset-password",
     *     tags={"Password Reset"},
     *     summary="Reset the user's password using a token",
     *     operationId="resetPassword",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token", "email", "password", "password_confirmation"},
     *             @OA\Property(
     *                 property="token",
     *                 type="string",
     *                 example="abc123xyz456...",
     *                 description="The password reset token from the email"
     *             ),
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 format="email",
     *                 example="user@example.com",
     *                 description="The user's email address"
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 type="string",
     *                 format="password",
     *                 example="newpassword123",
     *                 description="The new password (minimum 8 characters)"
     *             ),
     *             @OA\Property(
     *                 property="password_confirmation",
     *                 type="string",
     *                 format="password",
     *                 example="newpassword123",
     *                 description="Confirmation of the new password"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Your password has been successfully reset."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid token or user not found",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="The reset token is invalid or has expired.",
     *                 enum={
     *                     "The reset token is invalid or has expired.",
     *                     "No user found with this email address.",
     *                     "Failed to reset the password."
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="The given data was invalid."
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="password",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string",
     *                         example="The password field must be at least 8 characters."
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="Too many requests",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Too Many Attempts."
     *             )
     *         )
     *     )
     * )
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return match ($status) {
            Password::PASSWORD_RESET => response()->json(['message' => __('Your password has been successfully reset.')], 200),
            Password::INVALID_TOKEN => response()->json(['error' => __('The reset token is invalid or has expired.')], 400),
            Password::INVALID_USER => response()->json(['error' => __('No user found with this email address.')], 400),
            default => response()->json(['error' => __('Failed to reset the password.')], 400),
        };
    }
}