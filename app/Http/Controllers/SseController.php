<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class SseController extends Controller
{
    public function __construct()
    {
        //
    }

    public function stream()
    {
        $userId = auth('api')->user()->id;

        // $cacheKey = "sse_notifications_user_{$userId}";
        // $notifications = Cache::pull($cacheKey, []);
        // return $notifications;

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
