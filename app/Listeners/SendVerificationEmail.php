<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Mail\UserVerification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendVerificationEmail implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserRegistered $event): void
    {
        if(!$event->user->google_id){
            Mail::to($event->user->email)->queue(new UserVerification($event->user));
        }
    }
}
