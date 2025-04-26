<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreateUserPortfolio implements ShouldQueue
{
    public function handle(UserRegistered $event): void
    {
        $event->user->portfolio()->create([
            'user_id' => $event->user->id,
        ]);
        $event->user->setting()->create();
    }
}
