<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Tymon\JWTAuth\Facades\JWTAuth;

class SseController extends Controller
{
    public function __construct()
    {
        //
    }

    public function stream()
    {
        $token = request()->query('token'); // Get the token from the query string

        // If the token is invalid or missing, return an error
        if (!$token || !JWTAuth::setToken($token)->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = JWTAuth::toUser($token); // Get the authenticated user using the token
        $userId = $user->id;

        return response()->stream(function () use ($userId) {
            while (true) {
                $cacheKey = "sse_notifications_user_{$userId}";
                $notifications = Cache::pull($cacheKey, []);

                foreach ($notifications as $notification) {
                    echo "data: " . json_encode($notification) . "\n\n";
                }

                echo ":\n\n";

                ob_flush();
                flush();
                sleep(2);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
        ]);
    }
}
