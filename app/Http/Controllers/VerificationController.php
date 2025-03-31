<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Auth\Events\Verified;

class VerificationController extends Controller
{
    public function verify(Request $request)
    {
        $user = User::where('id', $request->id)->first(); 

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // if (!Hash::check($user->getEmailForVerification(), $request->hash)) {
        //     return response()->json(['message' => 'Invalid verification link.'], 400);
        // }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.'], 200);
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        return response()->json(['message' => 'Email verified successfully.'], 200);
    }

    public function resend(Request $request)
    {
        $request->user()->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Verification link sent!'
        ], 200);
    }
}
