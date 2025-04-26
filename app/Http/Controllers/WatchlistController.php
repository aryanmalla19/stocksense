<?php

namespace App\Http\Controllers;

use App\Http\Requests\WatchList\StoreWatchlistRequest;
use App\Http\Resources\WatchListResource;
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
}
