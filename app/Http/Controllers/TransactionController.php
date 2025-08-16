<?php

namespace App\Http\Controllers;

use App\Events\BroughtStock;
use App\Events\SoldStock;
use App\Http\Resources\TransactionResource;
use App\Models\Stock;
use App\Models\Transaction;
use App\Notifications\GeneralNotification;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Transactions",
 *     description="Endpoints for managing user stock transactions"
 * )
 */

/**
 * @OA\Schema(
 *     schema="Transaction",
 *     type="object",
 *     title="Transaction",
 *     description="A user's stock transaction record",
 * )
 */
class TransactionController extends Controller
{
     /**
     * @OA\Get(
     *     path="/api/v1/transactions",
     *     summary="List user's transactions",
     *     description="Fetch a paginated list of user's transactions with optional filters (type, date range).",
     *     tags={"Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filter by transaction type (buy, sell, ipo_allotted)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="from",
     *         in="query",
     *         description="Start date (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="to",
     *         in="query",
     *         description="End date (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated transactions list",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Transaction")
     *             )
     *         )
     *     ),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function index(Request $request)
    {
        $query = auth()->user()
            ->transactions()
            ->with('stock')
            ->orderBy('created_at', 'desc');

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('from') && $request->has('to')) {
            $query->whereBetween('created_at', [$request->from, $request->to]);
        }

        $transactions = $query->paginate(10);

        return TransactionResource::collection($transactions)
            ->additional([
                'message' => 'Successfully fetched filtered transactions',
            ]);
    }

     /**
     * @OA\Post(
     *     path="/api/v1/transactions",
     *     summary="Create a new transaction",
     *     description="Create a buy, sell, or ipo_allotted transaction for a stock.",
     *     tags={"Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"stock_id","type","quantity"},
     *             @OA\Property(property="stock_id", type="integer", example=2),
     *             @OA\Property(property="type", type="string", enum={"buy", "sell", "ipo_allotted"}, example="buy"),
     *             @OA\Property(property="quantity", type="integer", example=20)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Transaction created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Successfully created new transaction"),
     *             @OA\Property(property="data", ref="#/components/schemas/Transaction")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error or insufficient balance/holdings",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="You do not have enough balance in your portfolio.")
     *         )
     *     ),
     *     security={{"bearerAuth":{}}}
     * )
     */
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
        $attributes['transaction_fee'] = 0.01 * $total_price;

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
            'buy' => [
                event(new BroughtStock($transaction, $user)),
                $user->notify(new GeneralNotification('Buy Transaction', 'You successfully bought stocks ' . $transaction->stock->symbol . '.')),
            ],
            'sell' => [
                event(new SoldStock($transaction, $user)),
                $user->notify(new GeneralNotification('Sell Transaction', 'You successfully sold stocks ' . $transaction->stock->symbol . '.')),
            ],
        };

        return response()->json([
            'message' => 'Successfully created new transaction',
            'data' => new TransactionResource($transaction),
        ]);
    }

     /**
     * @OA\Get(
     *     path="/api/v1/transactions/{id}",
     *     summary="Get a specific transaction",
     *     description="Retrieve a transaction by its ID.",
     *     tags={"Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Transaction ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transaction found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Successfully fetched transaction data"),
     *             @OA\Property(property="data", ref="#/components/schemas/Transaction")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Transaction not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="No transaction found with ID 5")
     *         )
     *     ),
     *     security={{"bearerAuth":{}}}
     * )
     */
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
}
