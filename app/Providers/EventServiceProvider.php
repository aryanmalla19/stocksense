<?php

namespace App\Providers;

use App\Listeners\BroadcastNotificationSent;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        NotificationSent::class => [
            BroadcastNotificationSent::class,
        ],
    ];

    public function boot()
    {
        //dd('Event service provider is working');
    }
}