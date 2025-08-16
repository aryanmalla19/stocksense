<?php

namespace App\Http\Controllers;

use App\Http\Requests\PasswordReset\ResetPasswordRequest;
use App\Http\Requests\PasswordReset\SendResetPasswordRequest;
use App\Models\User;
use App\Notifications\PasswordResetNotification;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * @OA\Tag(
 *     name="Password Reset",
 *     description="API Endpoints for user password resetting"
 * )
 */
class PasswordResetController extends Controller
{
     /**
     * @OA\Post(
     *     path="/api/v1/forgot-password",
     *     summary="Send a password reset link to user's email",
     *     tags={"Password Reset"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"email"},
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 format="email",
     *                 example="user@example.com",
     *                 description="The email address associated with the user's account"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset link sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="A password reset link has been sent to your email.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="User not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Validation error or failure message"
     *             )
     *         )
     *     )
     * )
     */
    public function sendResetPassword(SendResetPasswordRequest $request)
    {

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => __('A password reset link has been sent to your email.')], 200)
            : response()->json(['error' => __('We cannot find a user with that email address.')], 400);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $data = $request->only('email', 'password', 'password_confirmation', 'token');
        $user = User::where('email', $data['email'])->first();
        if (Hash::check($data['password'], $user->password)) {
            return response()->json([
                'message' => 'Cannot set previous password',
            ], 400);
        }

        $status = Password::reset(
            $data,
            function (User $user, string $password) {

                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                $user->notify(new PasswordResetNotification);

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

        
    /**
     * @OA\Post(
     *     path="/api/v1/reset-password",
     *     summary="Reset the user's password",
     *     tags={"Password Reset"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"token", "email", "password", "password_confirmation"},
     *             @OA\Property(
     *                 property="token",
     *                 type="string",
     *                 example="your-password-reset-token",
     *                 description="Password reset token"
     *             ),
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 format="email",
     *                 example="user@example.com",
     *                 description="User's email address"
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 type="string",
     *                 format="password",
     *                 example="SecureP@ssw0rd",
     *                 description="New password (must contain uppercase, lowercase, number, special character)"
     *             ),
     *             @OA\Property(
     *                 property="password_confirmation",
     *                 type="string",
     *                 format="password",
     *                 example="SecureP@ssw0rd",
     *                 description="Confirm new password (must match password)"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Your password has been successfully reset.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Password reset error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Validation error or failure message"
     *           )
     *        )
     *     )
     * )
     */
    public function resetPasswordForm(Request $request)
    {
        return view('auth.reset-password', [
            'token' => $request->query('token'),
            'email' => $request->query('email'),
        ]);
    }
}
