<?php

namespace App\Jobs;

use App\Enums\IpoApplicationStatus;
use App\Mail\IpoAllottedMail;
use App\Models\IpoDetail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class AllotIpoJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public IpoDetail $ipoDetail)
    {
        //
    }

    public function handle(): void
    {
        try {
            $ipo = $this->ipoDetail;
            $totalShares = $ipo->total_shares;
            $applications = $ipo->applications()->get()->shuffle();

            $allotted = collect();
            $remainingShares = $totalShares;

            // Allot fixed 10 shares initially
            foreach ($applications as $app) {
                if ($remainingShares < 10 ) break;

                $allotted->push([
                    'id' => $app->id,
                    'user_id' => $app->user_id,
                    'current_allotment' => 10,
                    'applied_shares' => $app->applied_shares,
                ]);

                $app->update([
                    'status' => IpoApplicationStatus::Allotted,
                    'allotted_shares' => 10,
                ]);

                $remainingShares -= 10;
            }

            // Distribute extra shares one-by-one
            while ($remainingShares > 0) {
                $distributed = false;

                foreach ($allotted as &$entry) {
                    if ($remainingShares === 0) break;

                    if ($entry['current_allotment'] < $entry['applied_shares']) {
                        $entry['current_allotment']++;
                        $remainingShares--;
                        $distributed = true;
                    }
                }

                if (!$distributed) break; // Nobody can take more shares
            }


            // Final update
            foreach ($allotted as $entry) {
                $ipo->applications()->where('id', $entry['id'])->update([
                    'allotted_shares' => $entry['current_allotment'],
                ]);
                $refundAmount = ($entry['applied_shares'] - $entry['current_allotment']) * $ipo->issue_price;
                $user = \App\Models\User::find($entry['user_id']);
                $user?->portfolio->increment('amount', $refundAmount);
                Mail::to($user->email)->queue(
                    new IpoAllottedMail($ipo, $entry['current_allotment'])
                );
            }

            // Set the rest as not allotted
            $ipo->applications()
                ->whereNotIn('id', $allotted->pluck('id'))
                ->update([
                    'status' => IpoApplicationStatus::NotAllotted,
                    'allotted_shares' => 0,
                ]);

            $notAllotted = $ipo->applications()
                ->where('status', IpoApplicationStatus::NotAllotted)
                ->get();

            foreach ($notAllotted as $app) {
                $app->user->portfolio->increment('amount', $app->applied_shares * $ipo->issue_price);
            }


            $ipo->stock->forceFill(['is_listed' => true]);
            $ipo->update(['ipo_status' => 'allotted']);

            \Log::info("✅ IPO #{$ipo->id} - Allotment completed. Used shares: " . ($totalShares - $remainingShares));
        } catch (\Throwable $e) {
            \Log::error("❌ IPO allotment failed: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }


}
