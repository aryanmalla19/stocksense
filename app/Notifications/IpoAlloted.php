<?php
namespace App\Notifications;

use App\Models\IpoDetail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class IpoAlloted extends Notification
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
        return ['database']; // or add 'mail' if needed
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => "You have been allotted IPO of " . $this->ipoDetail->stock->symbol
        ];
    }
}
