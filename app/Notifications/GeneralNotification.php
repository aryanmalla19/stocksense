<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GeneralNotification extends Notification
{
    use Queueable;

    public $service;

    public $message;

    /**
     * Create a new notification instance.
     */
    public function __construct($service, $message)
    {
        $this->service = $service;
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        return [
            'service' => $this->service,
            'message' => $this->message,
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
