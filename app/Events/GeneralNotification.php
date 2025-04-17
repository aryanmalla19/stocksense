<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GeneralNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $type;
    public $message;
    public $data;

    public function __construct($type, $message, $data = [])
    {
        $this->type = $type;
        $this->message = $message;
        $this->data = $data;
    }

    public function broadcastOn()
    {
        return new Channel('general-notifications');
    }

    public function broadcastAs()
    {
        return 'GeneralNotification';
    }
}