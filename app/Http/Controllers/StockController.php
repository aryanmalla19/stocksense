<?php

namespace App\Http\Controllers;

use App\Http\Requests\Stock\StoreStockRequest;
use App\Http\Requests\Stock\UpdateStockRequest;
use App\Http\Resources\StockResource;
use App\Models\Stock;

class StockController extends Controller
{
    public function index()
    {
        $stocks = Stock::with(['sector', 'latestPrice'])->get();

        return response()->json([
            'message' => 'Successfully fetched all stocks',
            'data' => StockResource::collection($stocks),
        ]);
    }

    public function store(StoreStockRequest $request)
    {
        $stock = Stock::create($request->validated());

        return response()->json([
            'message' => 'Successfully registered stock',
            'data' => new StockResource($stock),
        ], 201);
    }

    public function show(string $id)
    {
        $stock = Stock::with(['sector', 'latestPrice'])->find($id);
        if (! $stock) {
            return response()->json([
                'message' => 'No Stock found with ID '.$id,
            ], 404);
        }

        return response()->json([
            'message' => 'Successfully fetched stock data',
            'data' => new StockResource($stock),
        ]);
    }

    public function update(UpdateStockRequest $request, string $id)
    {
        $stock = Stock::find($id);
        if (! $stock) {
            return response()->json([
                'message' => 'No Stock found with ID '.$id,
            ], 404);
        }

        $stock->update($request->validated());

        return response()->json([
            'message' => 'Stock successfully updated',
            'data' => new StockResource($stock),
        ]);
    }

    public function destroy(string $id)
    {
        $stock = Stock::find($id);
        if (! $stock) {
            return response()->json([
                'message' => 'No Stock found with ID '.$id,
            ], 404);
        }

        $stock->delete();

        return response()->json([
            'message' => 'Successfully deleted stock with ID '.$id,
        ]);
    }
}
