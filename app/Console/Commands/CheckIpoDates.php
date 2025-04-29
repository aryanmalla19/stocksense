<?php

namespace App\Console\Commands;

use App\Models\IpoDetail;
use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CheckIpoDates extends Command
{
    protected $signature = 'ipo:check-dates';
    protected $description = 'Check IPO dates and send notifications';

    public function handle()
    {
        $now = Carbon::now();

        // IPO Opening Notification
        $iposOpening = IpoDetail::whereDate('open_date', $now->toDateString())
            ->whereTime('open_date', '<=', $now->toTimeString())
            ->whereNull('opening_notified_at')
            ->get();

        foreach ($iposOpening as $ipo) {
            $this->sendNotification("IPO {$ipo->stock->symbol} is now open for application!");
            $ipo->update(['opening_notified_at' => $now]);
        }

        // IPO Closing Notification
        $iposClosing = IpoDetail::whereDate('close_date', $now->toDateString())
            ->whereTime('close_date', '<=', $now->toTimeString())
            ->whereNull('closing_notified_at')
            ->get();

        foreach ($iposClosing as $ipo) {
            $this->sendNotification("IPO {$ipo->stock->symbol} application is now closed!");
            $ipo->update(['closing_notified_at' => $now]);   
        }

        // IPO Listing Notification
        $iposListing = IpoDetail::whereDate('listing_date', $now->toDateString())
            ->whereTime('listing_date', '<=', $now->toTimeString())
            ->whereNull('listing_notified_at')
            ->get();

        foreach ($iposListing as $ipo) {
            $this->sendNotification("IPO {$ipo->stock->symbol} has been listed!");
            $ipo->update(['listing_notified_at' => $now]);   
        }
    }

    private function sendNotification($message)
    {
        $users = User::all();
        foreach ($users as $user) {
            $user->notify(new GeneralNotification('IPO detail', $message));
        }
    }
}