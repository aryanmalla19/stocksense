<?php

namespace App\Http\Controllers;

use App\Http\Resources\StockResource;
use App\Models\Stock;
use Illuminate\Http\Request;

class StockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stocks = Stock::with('sector')->get();
        return response()->json([
            'message' => 'Successfully fetched all stocks',
            'data' => StockResource::collection($stocks)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'symbol' => 'required|max:6|unique:stocks,symbol',
            'name' => 'required|string',
            'sector_id' => 'required|integer|exists:sectors,id'
        ]);

        $stock = Stock::create($data);
        return response()->json([
            'message' => 'Successfully registered stock',
            'data' => $stock
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $stock = Stock::with('sector')->find($id);
        if(empty($stock)){
            return response()->json([
                'message' => 'No Stock found with ID ' . $id,
            ],404);
        }

        return response()->json([
            'message' => 'Successfully fetched stock data',
            'data' => new StockResource($stock),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $stock = Stock::find($id);

        if (!$stock) {
            return response()->json([
                'message' => 'No Stock found with ID ' . $id,
            ], 404);
        }

        $data = $request->validate([
            'symbol' => 'sometimes|string|max:6|unique:stocks,symbol,' . $id,
            'name' => 'sometimes|string',
            'sector_id' => 'sometimes|integer|exists:sectors,id'
        ]);

        $stock->update($data);

        return response()->json([
            'message' => 'Stock successfully updated',
            'data' => new StockResource($stock),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $stock = Stock::find($id);

        if(empty($stock)){
            return response()->json([
                'message' => 'No Stock found with ID ' . $id,
            ],404);
        }
        $stock->delete();
        return response()->json([
            'message' => 'Successfully deleted stock with ID ' . $id,
        ]);
    }
}
