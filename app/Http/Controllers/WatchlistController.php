<?php

namespace App\Http\Controllers;

use App\Http\Resources\WatchListResource;
use App\Models\Watchlist;
use Illuminate\Http\Request;

class WatchlistController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $watchlists = auth()->user()->watchlists;

        return response()->json([
            'message' => 'Successfully fetched all watchlist data',
            'data' => WatchListResource::collection($watchlists),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'stock_id' => 'required|exists:stocks,id',
        ]);
        $user = auth()->user();
        $exists = $user->watchlists()->where($data)->exists();
        if ($exists) {
            return response()->json(
            [
            'message' => 'Same watchlist already exists',
            ],
            409);
        }

        $watchlist = $user->watchlists()->create($data);
        return response()->json([
            'message' => 'Successfully added watchlist',
            'data' => new WatchListResource($watchlist), 
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

        $watchlist = Watchlist::where('id', $id)->with(['user', 'stock'])->first();

        if (!$watchlist) {
            return response()->json([
                'message' => 'No Watchlist found with ID ' . $id,
            ], 404);
        }

        return response()->json([
            'message' => 'Successfully fetched watchlist data',
            'data' => new WatchListResource($watchlist),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $watchlist = Watchlist::where('id', $id)->first();
        if (!$watchlist) {
            return response()->json([
                'message' => 'No watchlist found with ID ' . $id,
            ], 404);
        }

        Watchlist::where('id', $id)->delete();

        return response()->json([
            'message' => 'Successfully deleted watchlist with ID ' . $id,
        ]);
    }

    public function showAll(){
        $watchlists = Watchlist::with(['user', 'stock'])->get();
        if(empty($watchlists)){
            return response()->json([
                'message' => 'No any watchlist found'
            ]);
        }
        return response()->json([
            'message' => 'Successfully fetched all watchlist data',
            'data' => WatchListResource::collection($watchlists)
        ]);
    }
}
