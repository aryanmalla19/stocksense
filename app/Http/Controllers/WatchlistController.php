<?php

namespace App\Http\Controllers;

use App\Http\Requests\WatchList\StoreWatchlistRequest;
use App\Http\Resources\WatchListResource;
use App\Models\Watchlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WatchlistController extends Controller
{
    public function index()
    {
        $watchlists = auth()->user()
            ->watchlists()
            ->with(['stock.latestPrice', 'stock.sector'])
            ->paginate(10);

        return WatchListResource::collection($watchlists);
    }


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
