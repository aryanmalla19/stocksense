<?php
namespace App\Listeners;

use App\Events\SoldStock;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Exception;

class RemoveHolding implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @throws \Exception
     */
    public function handle(SoldStock $event): void
    {
        $user = $event->user;
        $transaction = $event->transaction;
        $portfolio = $user->portfolio;

        if (!$portfolio) {
            throw new Exception('User does not have a portfolio.');
        }

        $holding = $portfolio->holdings()->where('stock_id', $transaction->stock_id)->first();

        if (!$holding) {
            throw new Exception("You don't own this stock to sell.");
        }

        if ($transaction->quantity > $holding->quantity) {
            throw new Exception("You can't sell more than you own. Available: {$holding->quantity}, Tried: {$transaction->quantity}");
        }

        // Add the money from the sale to portfolio amount
        $portfolio->amount += $transaction->price * $transaction->quantity;
        $portfolio->save();

        $newQuantity = $holding->quantity - $transaction->quantity;

        if ($newQuantity > 0) {
            $holding->update([
                'quantity' => $newQuantity,
            ]);
        } else {
            $holding->delete();
        }
    }
}
