<?php

namespace App\Http\Controllers;

use App\Events\BroughtStock;
use App\Events\SoldStock;
use App\Http\Resources\TransactionResource;
use App\Models\Stock;
use App\Models\Transaction;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Transactions",
 *     description="Endpoints for managing user stock transactions"
 * )
 */
class TransactionController extends Controller
{
    public function index()
    {
        $transactions = auth()->user()
            ->transactions()
            ->with('stock')
            ->get();

        return response()->json([
            'message' => 'Successfully fetched all transactions',
            'data' => TransactionResource::collection($transactions),
        ]);
    }

    public function store(Request $request)
    {
        $attributes = $request->validate([
            'stock_id' => 'required|integer|exists:stocks,id',
            'type' => 'required|in:buy,sell,ipo_allotted',
            'quantity' => 'required|integer|min:10',
        ]);

        $user = auth()->user();
        $price = Stock::find($attributes['stock_id'])->latestPrice->current_price;
        $total_price = $price * $attributes['quantity'];

        $attributes['price'] = $price;
        $attributes['transaction_fee'] = 0.05 * $total_price;

        if ($attributes['type'] === 'buy') {
            if (! $user->portfolio || $user->portfolio->amount < $total_price) {
                return response()->json([
                    'message' => 'You do not have enough balance in your portfolio.',
                ], 400);
            }
        }

        if ($attributes['type'] === 'sell') {
            $holding = $user->portfolio->holdings()
                ->where('stock_id', $attributes['stock_id'])
                ->first();

            if (! $holding || $holding->quantity < $attributes['quantity']) {
                return response()->json([
                    'message' => 'You are trying to sell more shares than you own or stock not present in your portfolio.',
                ], 400);
            }
        }

        // Create transaction
        $transaction = $user->transactions()->create($attributes);
        $transaction->load('stock');

        // Dispatch appropriate event
        match ($transaction->type) {
            'buy' => event(new BroughtStock($transaction, $user)),
            'sell' => event(new SoldStock($transaction, $user)),
        };

        return response()->json([
            'message' => 'Successfully created new transaction',
            'data' => new TransactionResource($transaction),
        ]);
    }

    public function show(string $id)
    {
        $transaction = Transaction::with(['user', 'stock'])->find($id);

        if (empty($transaction)) {
            return response()->json([
                'message' => 'No transaction found with ID '.$id,
            ], 404);
        }

        return response()->json([
            'message' => 'Successfully fetched transaction data',
            'data' => new TransactionResource($transaction),
        ]);
    }

    public function update(Request $request, string $id)
    {
        //        $transaction = Transaction::find($id);
        //
        //        if (! $transaction) {
        //            return response()->json([
        //                'message' => 'No Stock found with ID '.$id,
        //            ], 404);
        //        }
        //
        //        $data = $request->validate([
        //            'symbol' => 'sometimes|string|max:6|unique:stocks,symbol,'.$id,
        //            'name' => 'sometimes|string',
        //            'sector_id' => 'sometimes|integer|exists:sectors,id',
        //        ]);
        //
        //        $transaction->forceFill([
        //            'user_id' => $request->user_id,
        //            'stock_id' => $request->stock_id,
        //            'type' => $request->type,
        //            'quantity' => $request->quantity,
        //            'price' => $request->price,
        //            'transaction_fee' => $request->transaction_fee,
        //        ]);
        //        $transaction->save();
        //        $transaction->load('stock');
        //
        //        return response()->json([
        //            'message' => 'Stock successfully updated',
        //            'data' => new TransactionResource($transaction),
        //        ]);
    }

    public function destroy(string $id)
    {
        $transaction = Transaction::find($id);

        if (empty($transaction)) {
            return response()->json([
                'message' => 'No transaction found with ID '.$id,
            ], 404);
        }
        $transaction->delete();

        return response()->json([
            'message' => 'Successfully deleted transaction with ID '.$id,
        ]);
    }
}
