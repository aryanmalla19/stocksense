<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    /**
     * Display a listing of the user's notifications.
     */
    public function __invoke(): JsonResponse
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

    public function update(string $id)
    {
        $user = auth('api')->user();

        $notification = $user->notifications()->where('id', $id)->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification does not belong to the user.',
            ], 404);
        }

        if (!is_null($notification->read_at)) {
            return response()->json([
                'success' => false,
                'message' => 'Already marked as read',
            ]);
        }
        $notification->markAsRead();
        return response()->json([
            'success' => true,
            'message' => 'Successfully marked notification as read.',
            'data' => new NotificationResource($notification),
        ]);
    }


    public function show(string $id)
    {
        $user = auth('api')->user();

        $notification = $user->notifications()->where('id', $id)->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification does not belong to the user.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully fetched notification data',
            'data' => new NotificationResource($notification),
        ]);
    }
}
