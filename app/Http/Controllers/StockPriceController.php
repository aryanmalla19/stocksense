<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\StockPrice;
use Illuminate\Http\Request;

class StockPriceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stocks = Stock::with('prices')->get();
        return response()->json([
            'message' => 'Successfully fetched all stock with its prices',
            'data' => $stocks
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'stock_id' => 'required|exists:stocks,id',
            'price' => 'required|numeric'
        ]);

        $stock = Stock::find($data['stock_id']);

        if (!$stock) {
            return response()->json([
                'message' => 'Could not find stock with Id ' . $data['stock_id'],
            ], 404);
    }

    $newPrice = $stock->prices()->create($data);

    return response()->json([
        'message' => 'Successfully created new stock price',
        'data' => $newPrice,
    ], 201);
}


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $stockPrice = StockPrice::with('stock')->where('id', $id)->first();
        if(empty($stockPrice)){
            return response()->json([
                'message' => 'Could not find stock price data with ID ' . $id,
            ], 404);
        }

        return response()->json([
            'message' => 'Successfully fetched stock price data with ID ' . $id,
            'data' => $stockPrice
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        return response()->json([
            'message' => 'You cannot change stock previous price'
        ], 400);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return response()->json([
            'message' => 'You cannot delete stock previous price'
        ], 400);
    }
}
