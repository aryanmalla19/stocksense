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
    public function index()
    {
        $user = auth('api')->user();

        $notifications = $user->notifications()
            ->orderByRaw('read_at IS NULL DESC')
            ->orderBy('created_at', 'desc');

        $perPage = request('per_page', 10);
        $paginated = $notifications->paginate($perPage);

        return NotificationResource::collection($paginated)
            ->additional([
                'message' => 'Successfully fetched user\'s notifications',
                'meta' => [
                    'current_page' => $paginated->currentPage(),
                    'last_page' => $paginated->lastPage(),
                    'per_page' => $paginated->perPage(),
                    'total' => $paginated->total(),
                ]
            ]);
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
