<?php

namespace App\Http\Controllers;

use App\Http\Resources\TransactionResource;
use App\Models\Transaction;

use App\Http\Requests\Transaction\StoreTransactionRequest;
use App\Http\Requests\Transaction\UpdateTransactionRequest;


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

    
    public function store(StoreTransactionRequest $request)
    {
        $transaction = Transaction::create($request->validated());
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

    
    public function update(UpdateTransactionRequest $request, string $id)
    {
        $transaction = Transaction::find($id);

        if (!$transaction) {
            return response()->json([
                'message' => 'No Stock found with ID ' . $id,
            ], 404);
        }

        $transaction->update($request->validated());
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