<?php

namespace App\Jobs;

use App\Enums\IpoApplicationStatus;
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
    public function handle(): void
    {
        try {
            $ipo = $this->ipo;
            $totalShares = $ipo->total_shares;

            $applications = $ipo->applications()->get(); // Get all applicants
            $shuffled = $applications->shuffle();

            $remainingShares = $totalShares;
            $allotted = collect();

            // Initial allotment: Exactly 10 shares per selected applicant
            foreach ($shuffled as $app) {
                if ($remainingShares < 10) break;
                if ($app->applied_shares < 10) continue;

                $allot = 10; // Fixed allotment of 10 shares

                $app->update([
                    'status' => IpoApplicationStatus::Allotted,
                    'allotted_shares' => $allot,
                ]);

                $remainingShares -= $allot;
                $allotted->push([
                    'id' => $app->id,
                    'user_id' => $app->user_id,
                    'current_allotment' => $allot,
                    'applied_shares' => $app->applied_shares,
                ]);
            }

            // Patch: Distribute leftover shares (1-by-1) to already allotted users
            while ($remainingShares > 0) {
                $extraDistributed = false;

                foreach ($allotted as &$entry) {
                    if ($remainingShares <= 0) break;
                    if ($entry['current_allotment'] < $entry['applied_shares']) {
                        $entry['current_allotment'] += 1;
                        $remainingShares--;
                        $extraDistributed = true;
                    }
                }

                if (!$extraDistributed) break; // No one can take more shares
            }

            // Final update after patch
            foreach ($allotted as $entry) {
                $ipo->applications()->where('id', $entry['id'])->update([
                    'status' => IpoApplicationStatus::Allotted,
                    'allotted_shares' => $entry['current_allotment'],
                ]);
            }

            // Mark the rest as not_allotted
            $ipo->applications()
                ->whereNotIn('id', collect($allotted)->pluck('id'))
                ->update([
                    'status' => IpoApplicationStatus::NotAllotted,
                    'allotted_shares' => 0,
                ]);

            $ipo->stock->update(['is_listed' => true]);
            $ipo->update(['ipo_status' => 'allotted']);

            \Log::info("✅ Allotment job completed successfully for IPO ID: {$ipo->id} | Total shares used: " . ($totalShares - $remainingShares));
        } catch (\Throwable $e) {
            \Log::error("❌ Allotment job failed: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

}
