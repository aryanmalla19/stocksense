<?php

namespace App\Http\Controllers;

use App\Http\Resources\WatchListResource;
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
