<?php

namespace App\Http\Controllers;

use App\Mail\UserVerification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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
