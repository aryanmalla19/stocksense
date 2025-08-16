<?php

namespace App\Providers;

use App\Listeners\BroadcastNotificationSent;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Notifications\Events\NotificationSent;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        NotificationSent::class => [
            BroadcastNotificationSent::class,
        ],
    ];

    public function boot()
    {
        // dd('Event service provider is working');
    }
}
