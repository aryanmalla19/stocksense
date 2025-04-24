<?php

namespace App\Jobs;

use App\Enums\IpoApplicationStatus;
use App\Mail\IpoAllottedMail;
use App\Models\IpoDetail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class AllotIpoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public IpoDetail $ipoDetail)
    {
        //
    }

    public function handle(): void
    {
        try {
            $ipo = $this->ipoDetail;
            $totalShares = $ipo->total_shares;
            $applications = $ipo->applications()->with('user.portfolio.holdings')->get()->shuffle();

            $totalApplied = $applications->sum('applied_shares');
            Log::info($totalApplied);
            Log::info($totalShares);

            if ($totalApplied <= $totalShares) {
                foreach ($applications as $app) {
                    $app->update([
                        'status' => IpoApplicationStatus::Allotted,
                        'allotted_shares' => $app->applied_shares,
                    ]);

                    $user = $app->user;
                    if ($user?->portfolio) {
                        $user->portfolio->holdings()->create([
                            'average_price' => $ipo->issue_price,
                            'stock_id' => $ipo->stock->id,
                            'quantity' => $app->applied_shares,
                        ]);

                        Mail::to($user->email)->queue(new IpoAllottedMail($ipo, $app->applied_shares, $user));
                    }
                }
            } else {
                $allottedCollection = collect();
                $remainingShares = $totalShares;

                // Initial 10 shares allotment
                foreach ($applications as $app) {
                    if ($remainingShares < 10) break;

                    $app->update([
                        'status' => IpoApplicationStatus::Allotted,
                        'allotted_shares' => 10,
                    ]);

                    $allottedCollection->push([
                        'id' => $app->id,
                        'user_id' => $app->user_id,
                        'current_allotment' => 10,
                        'applied_shares' => $app->applied_shares,
                    ]);

                    $remainingShares -= 10;
                }

                Log::info("The remaining shares are " . $remainingShares);

                // Distribute remaining shares one by one
                $allotted = $allottedCollection->toArray(); // convert to mutable array

                while ($remainingShares > 0) {
                    $distributed = false;

                    Log::info("The remaining shares are :- " . $remainingShares);
                    foreach ($allotted as $i => $entry) {
                        if ($remainingShares === 0) break;
                        if ($entry['current_allotment'] < $entry['applied_shares']) {
                            $allotted[$i]['current_allotment']++;
                            $remainingShares--;
                            $distributed = true;
                        }
                        Log::info(" The current_allotment is " . $allotted[$i]['current_allotment']);
                        Log::info(" The applied_shares is " . $allotted[$i]['applied_shares']);
                    }

                    if (!$distributed) break;
                }

                Log::info("The remaining shares are : " . $remainingShares);

                // Final update of applications
                foreach ($allotted as $entry) {
                    $ipo->applications()->where('id', $entry['id'])->update([
                        'allotted_shares' => $entry['current_allotment'],
                        'status' => IpoApplicationStatus::Allotted,
                    ]);

                    $user = User::with('portfolio.holdings')->find($entry['user_id']);
                    if ($user?->portfolio) {
                        $user->portfolio->holdings()->create([
                            'average_price' => $ipo->issue_price,
                            'stock_id' => $ipo->stock->id,
                            'quantity' => $entry['current_allotment'],
                        ]);

                        $refundAmount = ($entry['applied_shares'] - $entry['current_allotment']) * $ipo->issue_price;
                        $user->portfolio->increment('amount', $refundAmount);

                        Mail::to($user->email)->queue(new IpoAllottedMail($ipo, $entry['current_allotment'], $user));
                    }
                }

                // Mark non-allotted applications
                $allottedIds = collect($allotted)->pluck('id');
                $nonAllottedIds = $applications->pluck('id')->diff($allottedIds);

                $ipo->applications()
                    ->whereIn('id', $nonAllottedIds)
                    ->update([
                        'status' => IpoApplicationStatus::NotAllotted,
                        'allotted_shares' => 0,
                    ]);

                foreach ($applications->whereIn('id', $nonAllottedIds) as $app) {
                    $user = $app->user;
                    if ($user?->portfolio) {
                        $user->portfolio->increment('amount', $app->applied_shares * $ipo->issue_price);
                    }
                }
            }

            // Final stock listing and price setup
            $ipo->stock->forceFill(['is_listed' => true])->save();
            $ipo->stock->prices()->create([
                'volume' => $ipo->total_shares,
                'open_price' => $ipo->issue_price,
                'close_price' => $ipo->issue_price,
                'high_price' => $ipo->issue_price,
                'low_price' => $ipo->issue_price,
                'current_price' => $ipo->issue_price,
            ]);
            $ipo->update(['ipo_status' => 'allotted']);

            Log::info("✅ IPO #{$ipo->id} - Allotment completed. Total shares used: " . $totalShares);
        } catch (\Throwable $e) {
            Log::error("❌ IPO allotment failed: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
