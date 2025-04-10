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
        $notifications = auth('api')->user()->notifications;

        if ($notifications->isEmpty()) {
            return response()->json(['message' => 'No notification found'], 200);
        }

        return response()->json([
            'message' => 'Successfully fetched user notifications',
            'data' => NotificationResource::collection($notifications)
        ], 200);
    }
}
