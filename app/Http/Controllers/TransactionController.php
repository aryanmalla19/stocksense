<?php

namespace App\Http\Controllers;

use App\Events\StockUpdated;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\Portfolio;
use App\Models\Transaction;
use App\Models\Stock;
class TransactionController extends Controller
{
    public function buy(){
        $attributes = request()->validate([
            'symbol'  => ['required', 'string'],
            'quantity' => ['required', 'integer', 'min:1'],
            'price'    => ['required', 'numeric', 'min:0.01'],
            'timestamp'=>['required', 'integer'],
            'type'=>['required', 'integer'],
        ]);

        $userId = Auth::id();
        
        $stockId = Stock::where('symbol', $attributes['symbol'])->value('id');

        //create new transaction
        Transaction::create([
            'user_id' => $userId,
            'stock_id' => $stockId,
            'quantity'=> $attributes['quantity'],
            'type'=> $attributes['type'],
            'price'=> $attributes['price'],
            'timestamp' => now(),
        ]);

        //updates if stock is already present in the user's portfolio
        $portfolio = Portfolio::where('user_id', $userId)
                        ->where('stock_id', $stockId)
                        ->first();     
        
        if($portfolio){
            $totalQuantity = $portfolio->quantity + $attributes['quantity'];
            $adjustedPrice = ($portfolio->price*$portfolio->quantity + $attributes['quantity']*$attributes['price'])/$totalQuantity;

            $portfolio->update([
                'quantity' => $totalQuantity,
                'price' => $adjustedPrice
            ]);
        }
        else{
            Portfolio::create([
                'user_id'=>$userId,
                'stock_id'=>$stockId,
                'quantity' => $attributes['quantity'],
                'price'=>$attributes['price']
            ]);
        }
        $stock = Stock::find($stockId);
        broadcast(new StockUpdated($stock));
              
    }

    public function sell(){
            $attributes = request()->validate([
            'symbol'  => ['required', 'string'],
            'quantity' => ['required', 'integer', 'min:1'],
            'price'    => ['required', 'numeric', 'min:0.01']
        ]);

    $userId = Auth::id();
        
        $stockId = Stock::where('symbol', $attributes['symbol'])->first();

        //create new transaction
        Transaction::create([
            'user_id' => $userId,
            'stock_id' => $stockId,
            'quantity'=> $attributes['quantity'],
            'type'=> $attributes['type'],
            'price'=> $attributes['price'],
            'timestamp' => now(),
        ]);

        //updates if stock is already present in the user's portfolio
        $portfolio = Portfolio::where('user_id', $userId)
                        ->where('stock_id', $stockId)
                        ->first();     
        
        if ($portfolio) {
            $remainingQuantity = $portfolio->quantity - $attributes['quantity'];

            if ($remainingQuantity > 0) {
                $adjustedPrice = ($portfolio->price * $portfolio->quantity - $attributes['quantity'] * $attributes['price']) / $remainingQuantity;
                $portfolio->update([
                    'quantity' => $remainingQuantity,
                    'price'    => $adjustedPrice
                ]);
            } else {
                $portfolio->delete(); // ✅ Remove stock from portfolio if fully sold
            }

            return response()->json(['message' => 'Stock sold successfully.'], 200);
        } else {
            return response()->json([
                'message' => 'Stock does not exist in your portfolio!',
                'order'   => $attributes
            ], 400);
        }
    }
}   