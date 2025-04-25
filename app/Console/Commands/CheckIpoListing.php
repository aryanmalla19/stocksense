<?php

namespace App\Console\Commands;

use App\Jobs\AllotIpoJob;
use App\Models\IpoDetail;
use Illuminate\Console\Command;

class CheckIpoListing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ipo:check-listings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and process IPO listings';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        try {
            $now = now()->format('Y-m-d H:i:00');

            $ipos = IpoDetail::where('listing_date', $now)
                ->whereIn('ipo_status', ['opened', 'pending', 'closed'])
                ->get();

            foreach ($ipos as $ipo) {
                AllotIpoJob::dispatch($ipo);
            }

            $this->info('Checked IPOs and dispatched '.$ipos->count().' allotment jobs.');
            \Log::info('ipo:check-listings ran successfully, dispatched '.$ipos->count().' jobs.');
        } catch (\Exception $e) {
            \Log::error('Error in ipo:check-listings: '.$e->getMessage());
            $this->error('An error occurred while checking IPOs.');
        }
    }
}
