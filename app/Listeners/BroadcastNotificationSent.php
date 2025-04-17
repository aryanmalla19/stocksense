<?php

namespace App\Listeners;
use App\Events\GeneralNotification;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Log;

class BroadcastNotificationSent
{
    public function handle(NotificationSent $event)
    {
        if ($event->channel === 'database') {
            $notificationData = $event->notification->toArray($event->notifiable);

            $type = class_basename($event->notification);

            $message = $notificationData['message'] ?? 'No message available';

            $data = array_merge($notificationData, [
                'notification_id' => $event->response,
                'user_id' => $event->notifiable->id,
            ]);

            event(new GeneralNotification($type, $message, $data));

        }
    }
}
