<?php

namespace App\Listeners;

use App\Events\BroughtStock;
use Illuminate\Contracts\Queue\ShouldQueue;

class AddHoldings implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(BroughtStock $event): void
    {
        $user = $event->user;
        $transaction = $event->transaction;
        $portfolio = $user->portfolio;

        $portfolio->amount -= ($transaction->price * $transaction->quantity) + $transaction->transaction_fee;
        $portfolio->save();

        $holding = $portfolio->holdings()->where('stock_id', $transaction->stock_id)->first();

        if ($holding) {
            $totalQuantity = $holding->quantity + $transaction->quantity;
            $totalCost = ($holding->quantity * $holding->average_price) + ($transaction->quantity * $transaction->price);
            $newAvgPrice = $totalCost / $totalQuantity;

            $holding->update([
                'quantity' => $totalQuantity,
                'average_price' => $newAvgPrice,
            ]);
        } else {
            $portfolio->holdings()->create([
                'stock_id' => $transaction->stock_id,
                'quantity' => $transaction->quantity,
                'average_price' => $transaction->price,
            ]);
        }
    }
}
