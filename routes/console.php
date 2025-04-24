<?php

use App\Console\Commands\CheckIpoListing;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule the command
Schedule::command('ipo:check-listings')->everyMinute();
Schedule::command('stocks:update-prices')->everyFiveSeconds();
