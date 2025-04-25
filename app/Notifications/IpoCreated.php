<?php

namespace App\Notifications;

use App\Models\IpoDetail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class IpoCreated extends Notification
{
    use Queueable;

    protected $ipoDetail;

    /**
     * Create a new notification instance.
     */
    public function __construct(IpoDetail $ipo_detail)
    {
        $this->ipoDetail = $ipo_detail;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'New Ipo has been opened '.$this->ipoDetail->stock->symbol,
        ];
    }
}
