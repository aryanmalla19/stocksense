<?php

namespace App\Http\Controllers;

use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\Request;


class TransactionController extends Controller
{
    
    public function index()
    {
        $transactions = Transaction::with(['user', 'stock'])->get();

        return response()->json([
            'message' => 'Successfully fetched all transactions',
            'data' => TransactionResource::collection($transactions),
        ]);
    }

    
    public function store(Request $request)
    {
        $attributes = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'stock_id' => 'required|integer|exists:stocks,id',
            'type' => 'required|in:buy,sell,ipo_allotted',
            'quantity' => 'required|integer|min:10',
            'price' => 'required',
            'transaction_fee' => 'required'
        ]);

        $transaction = Transaction::create($attributes);

        $transaction->load('stock');

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
                'message' => 'No transaction found with ID ' . $id,
            ], 404);
        }

        return response()->json([
            'message' => 'Successfully fetched transaction data',
            'data' => new TransactionResource($transaction),
        ]);
    }

    
    public function update(Request $request, string $id)
    {
        $transaction = Transaction::find($id);

        if (!$transaction) {
            return response()->json([
                'message' => 'No Stock found with ID ' . $id,
            ], 404);
        }

        $data = $request->validate([
            'symbol' => 'sometimes|string|max:6|unique:stocks,symbol,' . $id,
            'name' => 'sometimes|string',
            'sector_id' => 'sometimes|integer|exists:sectors,id',
        ]);

        $transaction->forceFill([
            'user_id' => $request->user_id,
            'stock_id' => $request->stock_id,
            'type' => $request->type,
            'quantity' => $request->quantity,
            'price' => $request->price,
            'transaction_fee' => $request->transaction_fee
        ]);
        $transaction->save();
        $transaction->load('stock');

        return response()->json([
            'message' => 'Stock successfully updated',
            'data' => new TransactionResource($transaction),
        ]);
    }

    
    public function destroy(string $id)
    {
        $transaction = Transaction::find($id);

        if (empty($transaction)) {
            return response()->json([
                'message' => 'No transaction found with ID ' . $id,
            ], 404);
        }
        $transaction->delete();

        return response()->json([
            'message' => 'Successfully deleted transaction with ID ' . $id,
        ]);
    }
}