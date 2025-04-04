<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\URL;
use Tymon\JWTAuth\Facades\JWTAuth;

class VerificationEmailController extends Controller
{
    /**
     * Verify the user's email address.
     *
     * @param Request $request
     * @param int $id
     * @param string $hash
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        // Validate the signed URL
        if (!URL::hasValidSignature($request)) {
            return response()->json(['error' => 'Invalid or expired verification link'], 401);
        }

        // Verify the hash matches the user's email
        if (!hash_equals((string) $hash, sha1($user->email))) {
            return response()->json(['error' => 'Invalid verification link'], 401);
        }

        // Check if already verified
        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified'], 400);
        }

        // Mark email as verified and fire event
        $user->markEmailAsVerified();
        event(new Verified($user));

        // Optionally generate a JWT token for immediate login
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'Email verified successfully',
            'token' => $token, // Return JWT token for API clients
        ], 200);
    }

    /**
     * Resend the email verification notification.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resend(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();

        if ($user && !$user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
            return response()->json(['message' => 'Verification email resent.']);
        }

        return response()->json(['message' => 'User not found or already verified.'], 400);
    }
}
