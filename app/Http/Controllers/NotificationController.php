<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

/**
 * @OA\Tag(
 *     name="Notification",
 *     description="API Endpoints for managing user notifications"
 * )
 */

 /**
 * @OA\Schema(
 *     schema="Notification",
 *     title="Notification",
 *     description="User notification schema",
 *     type="object",
 *     required={"time", "notification"},
 *     @OA\Property(
 *         property="time",
 *         type="string",
 *         format="date-time",
 *         example="2025-04-26T12:30:00.000000Z",
 *         description="The time when the notification was created"
 *     ),
 *     @OA\Property(
 *         property="notification",
 *         type="string",
 *         example="Your order has been shipped!",
 *         description="Notification message"
 *     )
 * )
 */
class NotificationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/users/notifications",
     *     summary="Get User Notifications",
     *     description="Fetch a list of notifications for the authenticated user",
     *     operationId="getUserNotifications",
     *     tags={"Notifications"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched user notifications",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully fetched user's notifications"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Notification")),
     *             @OA\Property(property="meta", type="object", 
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=50)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="No notifications found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No notifications found")
     *         )
     *     ),
     * )
     */
    public function index(): JsonResponse
    {
        $user = auth('api')->user();

        $notifications = $user->notifications()
            ->orderByRaw('read_at IS NULL DESC')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        if ($notifications->isEmpty()) {
            return response()->json(['message' => 'No notifications found'], 200);
        }

        return response()->json([
            'message' => 'Successfully fetched user\'s notifications',
            'data' => NotificationResource::collection($notifications),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ]
        ], 200);
    }

     /**
     * @OA\Post(
     *     path="/api/v1/users/notifications/mark-all-as-read",
     *     summary="Mark All Notifications as Read",
     *     description="Mark all unread notifications for the authenticated user as read",
     *     operationId="markAllNotificationsAsRead",
     *     tags={"Notifications"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully marked all user's notifications as read",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully marked all user's notifications as read")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="No unread notifications to mark as read",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No unread notifications to mark as read")
     *         )
     *     ),
     * )
     */
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

     /**
     * @OA\Put(
     *     path="/api/v1/users/notifications/{id}",
     *     summary="Mark Notification as Read",
     *     description="Mark a specific notification as read for the authenticated user",
     *     operationId="markNotificationAsRead",
     *     tags={"Notifications"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Notification ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully marked notification as read",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Successfully marked notification as read"),
     *             @OA\Property(property="data", ref="#/components/schemas/Notification")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Notification not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Notification does not belong to the user.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Notification already marked as read",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Already marked as read")
     *         )
     *     )
     * )
     */
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

     /**
     * @OA\Get(
     *     path="/api/v1/users/notifications/{id}",
     *     summary="Get a Specific Notification",
     *     description="Fetch a specific notification by ID for the authenticated user",
     *     operationId="getNotification",
     *     tags={"Notifications"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Notification ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched notification",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Successfully fetched notification data"),
     *             @OA\Property(property="data", ref="#/components/schemas/Notification")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Notification not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Notification does not belong to the user.")
     *         )
     *     )
     * )
     */
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
