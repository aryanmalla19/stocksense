<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\SendResetPasswordRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;


class PasswordResetController extends Controller
{
    
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

    public function resetPasswordForm(Request $request)
    {
        return view('auth.reset-password', [
            'token' => $request->query('token'),
            'email' => $request->query('email'),
        ]);
    }
}
