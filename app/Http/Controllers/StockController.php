<?php

namespace App\Http\Controllers;

use App\Http\Requests\Stock\StoreStockRequest;
use App\Http\Requests\Stock\UpdateStockRequest;
use App\Http\Resources\StockResource;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index()
    {
        $stocks = Stock::with(['sector', 'latestPrice'])
            ->listed();

        if ($symbol = request('symbol')) {
            $symbol = strtoupper($symbol);
            $stocks->symbol($symbol);
        }

        if (request('column') && request('direction')) {
            $stocks->sortColumn(request('column'), request('direction'));
        }

        $perPage = request('per_page', 10); // default is 10
        $paginated = $stocks->paginate($perPage);

        return StockResource::collection($paginated)
            ->additional([
                'message' => 'Successfully fetched all stocks',
            ]);
    }

    public function sortStock(string $column, string $direction)
    {
        // Validate direction
        if (!in_array(strtolower($direction), ['asc', 'desc'])) {
            return response()->json([
                'message' => 'Invalid sort direction. Use "asc" or "desc".',
            ], 400);
        }

        // Validate column
        $validColumns = [
            'symbol', 'company_name', 'sector_id', 'is_listed',
            'open_price', 'close_price', 'high_price', 'low_price', 'current_price'
        ];
        if (!in_array($column, $validColumns)) {
            return response()->json([
                'message' => 'Invalid sort column',
            ], 400);
        }

        // Enable query logging
        DB::enableQueryLog();

        $stocks = Stock::select('stocks.*')
            ->with(['sector', 'latestPrice'])
            ->listed()
            ->sortColumn($column, $direction);

        // Debug: Log raw query
        \Log::info('Sort stock query:', DB::getQueryLog());
        DB::disableQueryLog();

        // Debug: Log query results before pagination
        \Log::info('Stocks retrieved before pagination:', $stocks->get()->toArray());

        $perPage = request('per_page', 10); // default is 10
        $paginated = $stocks->paginate($perPage);

        $response = StockResource::collection($paginated)
            ->additional([
                'message' => 'Stocks retrieved successfully',
            ]);

        // Debug: Log final response
        \Log::info('Sort stock response:', $response->response()->getData(true));

        return $response;
    }

    public function store(StoreStockRequest $request)
    {
        $stock = Stock::create($request->validated());

        return response()->json([
            'message' => 'Successfully registered stock',
            'data' => new StockResource($stock->load('sector')),
        ], 201);
    }

    public function show(string $id)
    {
        $stock = Stock::with([
            'sector',
            'latestPrice',
            'prices' => function ($query) {
                $query->orderBy('date', 'asc');
            }
        ])
            ->listed()
            ->find($id);

        if (!$stock) {
            return response()->json([
                'message' => 'No listed stock found with ID ' . $id,
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
        if (!$stock) {
            return response()->json([
                'message' => 'No Stock found with ID ' . $id,
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
        if (!$stock) {
            return response()->json([
                'message' => 'No Stock found with ID ' . $id,
            ], 404);
        }

        $stock->delete();

        return response()->json([
            'message' => 'Successfully deleted stock with ID ' . $id,
        ]);
    }
}