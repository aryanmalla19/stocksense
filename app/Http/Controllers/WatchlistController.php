<?php

namespace App\Http\Controllers;

use App\Http\Requests\WatchList\StoreWatchlistRequest;
use App\Http\Resources\WatchListResource;
use App\Models\Watchlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="Watchlist",
 *     description="Operations related to user's Watchlist"
 * )
 */

/**
 * @OA\Schema(
 *     schema="Watchlist",
 *     title="Watchlist",
 *     description="A stock added to user's watchlist",
 *     type="object",
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="stock_id", type="integer", example=101),
 *     @OA\Property(
 *         property="stock",
 *         type="object",
 *         description="Stock details",
 *         @OA\Property(property="id", type="integer", example=101),
 *         @OA\Property(property="symbol", type="string", example="AAPL"),
 *         @OA\Property(property="name", type="string", example="Apple Inc."),
 *         @OA\Property(
 *             property="latestPrice",
 *             type="object",
 *             description="Latest stock price",
 *             @OA\Property(property="price", type="number", format="float", example=172.50),
 *             @OA\Property(property="change", type="number", format="float", example=-1.25)
 *         ),
 *         @OA\Property(
 *             property="sector",
 *             type="object",
 *             description="Sector of the stock",
 *             @OA\Property(property="id", type="integer", example=5),
 *             @OA\Property(property="name", type="string", example="Technology")
 *         )
 *     )
 * )
 */
class WatchlistController extends Controller
{
     /**
     * @OA\Get(
     *     path="/api/v1/watchlist",
     *     summary="Get all stocks in user's watchlist",
     *     tags={"Watchlist"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of watchlist entries",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully fetched all watchlist data"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Watchlist"))
     *         )
     *     )
     * )
     */
    public function index()
    {
        $watchlists = auth()->user()
            ->watchlists()
            ->with(['stock.latestPrice', 'stock.sector'])
            ->paginate(10);

        return WatchListResource::collection($watchlists);
    }

     /**
     * @OA\Post(
     *     path="/api/v1/watchlist",
     *     summary="Add a new stock to user's watchlist",
     *     tags={"Watchlist"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"stock_id"},
     *             @OA\Property(
     *                 property="stock_id",
     *                 type="integer",
     *                 description="ID of the stock to add",
     *                 example=101
     *             )
     *          )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successfully added watchlist",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully added watchlist"),
     *             @OA\Property(property="data", ref="#/components/schemas/Watchlist")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Watchlist already exists",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Same watchlist already exists")
     *         )
     *     )
     * )
     */
    public function store(StoreWatchlistRequest $request)
    {
        $data = $request->validated();
        $user = auth()->user();
        $exists = $user->watchlists()->where($data)->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Same watchlist already exists',
            ], 409);
        }

        $watchlist = $user->watchlists()->create($data)->load(['stock.latestPrice', 'stock.sector']);

        return response()->json([
            'message' => 'Successfully added watchlist',
            'data' => new WatchListResource($watchlist),
        ]);
    }

     /**
     * @OA\Delete(
     *     path="/api/v1/watchlist/{stock_id}",
     *     summary="Remove a stock from user's watchlist",
     *     tags={"Watchlist"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="stock_id",
     *         in="path",
     *         required=true,
     *         description="ID of the stock to remove from watchlist",
     *         @OA\Schema(type="integer", example=101)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully removed watchlist",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully removed watchlist")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Watchlist not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No watchlist found with Stock ID 101")
     *         )
     *     )
     * )
     */
    public function destroy(string $stockId)
    {
        $userId = auth()->id();

        $deleted = DB::table('watchlists')
            ->where('user_id', $userId)
            ->where('stock_id', $stockId)
            ->delete();

        if (! $deleted) {
            return response()->json([
                'message' => 'No watchlist found with Stock ID '.$stockId,
            ], 404);
        }

        return response()->json([
            'message' => 'Successfully removed watchlist',
        ]);
    }

    public function multipleDelete(Request $request)
    {
        $request->validate([
            'stock_ids' => 'required|array',
            'stock_ids.*' => 'integer|exists:watchlists,stock_id',
        ]);

        $user = auth('api')->user();

        $deleted = Watchlist::where('user_id', $user->id)
            ->whereIn('stock_id', $request->stock_ids)
            ->delete();

        if ($deleted) {
            return response()->json(['message' => 'Items deleted successfully'], 200);
        }

        return response()->json(['message' => 'No items deleted'], 404);
    }

}
