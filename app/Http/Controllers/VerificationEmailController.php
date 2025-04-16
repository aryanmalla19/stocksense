<?php

namespace App\Http\Controllers;

use App\Mail\UserVerification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class VerificationEmailController extends Controller
{
    /**
     * Verify the user's email address.
     *
     * @param  int  $id
     * @param  string  $hash
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verify(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        // Verify the hash matches the user's email
        if (! hash_equals((string) $hash, sha1($user->email))) {
            $redirectUrl = URL::temporarySignedRoute(
                'login.with-message',
                now()->addMinutes(30),
                ['error' => 'invalid_link']
            );
            return redirect()->to($redirectUrl);
        }

        // Check if already verified
        if ($user->hasVerifiedEmail()) {
            $redirectUrl = URL::temporarySignedRoute(
                'login.with-message',
                now()->addMinutes(30),
                ['message' => 'already_verified']
            );
            return redirect()->to($redirectUrl);
        }

        $user->markEmailAsVerified();

        $redirectUrl = URL::temporarySignedRoute(
            'login.with-message',
            now()->addMinutes(30),
            ['message' => 'email_verified']
        );

        return redirect()->to($redirectUrl);
    }

    /**
     * Resend the email verification notification.
     *
     * @return \Illuminate\Http\JsonResponse
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