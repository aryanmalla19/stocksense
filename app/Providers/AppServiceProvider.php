<?php

namespace App\Providers;

use App\Models\Holding;
use App\Policies\HoldingPolicy;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Holding::class, HoldingPolicy::class);

        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return (new MailMessage)
                ->subject('Verify Email Address')
                ->view('emails.custom-verify', ['url' => (string) $url, 'user' => $notifiable]);
        });
    }
}
