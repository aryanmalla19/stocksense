<?php

namespace App\Http\Controllers;

use App\Http\Requests\Stock\StoreStockRequest;
use App\Http\Requests\Stock\UpdateStockRequest;
use App\Http\Resources\StockResource;
use App\Models\Stock;
use Illuminate\Http\Request;

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

    public function sortStock($column, $direction = 'asc')
    {
        $allowedColumns = [
            'open_price' => 'stock_prices.open_price',
            'close_price' => 'stock_prices.close_price',
            'high_price' => 'stock_prices.high_price',
            'low_price' => 'stock_prices.low_price',
            'current_price' => 'stock_prices.current_price',
            'volume' => 'stock_prices.volume',
            'company_name' => 'stocks.company_name'
        ];

        if (!isset($allowedColumns[$column])) {
            return response()->json([
                'message' => 'Invalid sort column',
            ], 400);
        }

        $stocks = Stock::query()
            ->join('stock_prices', 'stocks.id', '=', 'stock_prices.stock_id')
            ->orderBy($allowedColumns[$column], $direction)
            ->with(['sector', 'latestPrice'])
            ->get();

        if (empty($stocks)) { 
            return response()->json([
                'message' => 'No stocks found'
            ], 200);
        }

        return response()->json([
            'message' => 'Stocks retrieved successfully',
            'data' => StockResource::collection($stocks)
        ], 200);
    }

    public function searchStock(Request $request){

        $request->validate([
            'query' => 'required',
        ]);

        $query = $request->input('query');

        $stocks = Stock::where('symbol', 'like', "%{$query}%")
        ->orWhere('company_name', 'like', "%{$query}%")
        ->with(['sector', 'latestPrice'])
        ->get();

        if (empty($stocks)) {
            return response()->json([
                'message' => 'No stocks found'
            ], 200);
        }

        return response()->json([
            'message' => 'Stocks retrieved successfully',
            'data' => StockResource::collection($stocks)
        ], 200);

    }
}
