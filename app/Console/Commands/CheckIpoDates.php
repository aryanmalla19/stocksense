<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckIpoDates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ipo:check-dates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check IPO dates and send notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
    }
}
