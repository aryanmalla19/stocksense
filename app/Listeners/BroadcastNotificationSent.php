<?php

namespace App\Listeners;

use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Cache;

class BroadcastNotificationSent
{
    public function handle(NotificationSent $event)
    {
        if ($event->channel === 'database') {

            if ($event->channel !== 'database') {
                return;
            }

            $userId = $event->notifiable->id;

            $data = $event->notification->toArray($event->notifiable);
            $data['notification_id'] = $event->notification->id ?? uniqid();
            $data['timestamp'] = now()->toDateTimeString();

            // Store it temporarily in cache
            $cacheKey = "sse_notifications_user_{$userId}";
            $existing = Cache::get($cacheKey, []);
            $existing[] = $data;

            Cache::put($cacheKey, $existing, 300);

        }
    }
}
