<?php

namespace App\Http\Controllers;

use App\Http\Resources\StockPriceResource;
use App\Http\Resources\StockResource;
use App\Http\Resources\StockWithPriceResource;
use App\Models\Stock;
use App\Models\StockPrice;
use Illuminate\Http\Request;

class StockPriceController extends Controller
{
    public function index()
    {
        $stocks = Stock::with('prices')->get();

        return response()->json([
            'message' => 'Successfully fetched all stock with its prices',
            'data' => StockWithPriceResource::collection($stocks),
        ]);
    }

    public function store(StoreStockPriceRequest $request)
    {
        $data = $request->validated();

        $stock = Stock::find($data['stock_id']);

        if (! $stock) {
            return response()->json([
                'message' => 'Could not find stock with Id '.$data['stock_id'],
            ], 404);
        }

        $newPrice = $stock->prices()->create($data);

        return response()->json([
            'message' => 'Successfully created new stock price',
            'data' => $newPrice,
        ], 201);
    }

    public function show(string $id)
    {
        $stockPrice = StockPrice::with('stock')->where('id', $id)->first();
        if (empty($stockPrice)) {
            return response()->json([
                'message' => 'Could not find stock price data with ID '.$id,
            ], 404);
        }

        return response()->json([
            'message' => 'Successfully fetched stock price data with ID '.$id,
            'data' => new StockPriceResource($stockPrice),
        ]);
    }

    public function update(Request $request, string $id)
    {
        return response()->json([
            'message' => 'You cannot change stock previous price',
        ], 400);
    }

    public function destroy(string $id)
    {
        return response()->json([
            'message' => 'You cannot delete stock previous price',
        ], 400);
    }

    public function historyStockPrices(string $id)
    {
        $stock = Stock::with(['prices', 'latestPrice', 'sector'])->find($id);

        if (! $stock) {
            return response()->json([
                'message' => 'Stock not found',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'message' => 'Successfully rendered stock all historically data',
            'data' => [
                'stock' => new StockResource($stock),
                'historic' => StockPriceResource::collection($stock->prices),
            ],
        ]);
    }
}
