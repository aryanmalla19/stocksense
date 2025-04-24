<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    /**
     * Display a listing of the user's notifications.
     */
    public function index(): JsonResponse
    {
        $user = auth('api')->user();

        $unread = $user->unreadNotifications;
        $read = $user->readNotifications;

        // unread first, then read
        $combined = $unread->merge($read); 

        if ($combined->isEmpty()) {
            return response()->json(['message' => 'No notifications found'], 200);
        }

        return response()->json([
            'message' => 'Successfully fetched user\'s notifications',
            'data' => NotificationResource::collection($combined),
        ], 200);
    }
    
    public function markAllAsRead(): JsonResponse
    {
        $user = auth('api')->user();

        if ($user->unreadNotifications->isEmpty()) {
            return response()->json([
                'message' => 'No unread notifications to mark as read',
            ], 200);
        }

        $user->unreadNotifications->markAsRead();

        return response()->json([
            'message' => 'Successfully marked all user\'s notifications as read',
        ], 200);
    }
}
