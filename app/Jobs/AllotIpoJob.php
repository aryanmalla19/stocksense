<?php

namespace App\Jobs;

use App\Models\IpoDetail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class AllotIpoJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public IpoDetail $ipo)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $ipo = $this->ipo;
        $totalShares = $ipo->total_shares;

        $applications = $ipo->applications()->get(); // get all applicants
        $shuffled = $applications->shuffle(); // random order

        $remainingShares = $totalShares;
        $allotted = collect();

        foreach ($shuffled as $app) {
            if ($remainingShares <= 0) break;

            $maxAllot = min(20, $remainingShares); // upper bound
            $allot = rand(10, $maxAllot); // random between 10 and maxAllot

            $app->update([
                'status' => 'allotted',
                'allotted_shares' => $allot,
            ]);

            $remainingShares -= $allot;
            $allotted->push($app->id);
        }

        // Mark rest as not_allotted
        $ipo->applications()
            ->whereNotIn('id', $allotted)
            ->update([
                'status' => 'not_allotted',
                'allotted_shares' => 0,
            ]);
        $ipo->stock->update(['is_listed' => true]);

        $ipo->update(['ipo_status' => 'allotted']);
    }
}
