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
        $applicants = $this->ipo->applications; // assuming relationship

        $winners = $applicants->shuffle()->take(10); // example: 10 random winners

        foreach ($winners as $user) {
            // assign shares to user
            // maybe create IpoAllotment::create([...]);
        }

    }
}
