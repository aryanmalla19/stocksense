<?php

namespace App\Http\Controllers;

use App\Http\Resources\StockPriceResource;
use App\Http\Resources\StockResource;
use App\Http\Resources\StockWithPriceResource;
use App\Models\Stock;
use App\Models\StockPrice;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Stock Prices",
 *     description="Endpoints for managing stock prices"
 * )
 */
class StockPriceController extends Controller
{
    /**
     * @OA\Get(
     *     path="/v1/stock-prices",
     *     tags={"Stock Prices"},
     *     summary="Get a list of all stocks with their prices",
     *     operationId="getStockPrices",
     *     @OA\Response(
     *         response=200,
     *         description="List of stocks with prices retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully fetched all stock with its prices"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="symbol", type="string", example="AAPL"),
     *                     @OA\Property(property="name", type="string", example="Apple Inc."),
     *                     @OA\Property(
     *                         property="prices",
     *                         type="array",
     *                         @OA\Items(
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="stock_id", type="integer", example=1),
     *                             @OA\Property(property="price", type="number", format="float", example=150.25),
     *                             @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-01T12:00:00Z")
     *                         )
     *                     ),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-01T12:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-01T12:00:00Z")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $stocks = Stock::with('prices')->get();

        return response()->json([
            'message' => 'Successfully fetched all stock with its prices',
            'data' => StockWithPriceResource::collection($stocks),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/v1/stock-prices",
     *     tags={"Stock Prices"},
     *     summary="Create a new stock price",
     *     operationId="createStockPrice",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"stock_id", "price"},
     *             @OA\Property(
     *                 property="stock_id",
     *                 type="integer",
     *                 example=1,
     *                 description="The ID of the stock"
     *             ),
     *             @OA\Property(
     *                 property="price",
     *                 type="number",
     *                 format="float",
     *                 example=150.25,
     *                 description="The price of the stock"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Stock price created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully created new stock price"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="stock_id", type="integer", example=1),
     *                 @OA\Property(property="price", type="number", format="float", example=150.25),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-01T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-01T12:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Stock not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Could not find stock with Id 1")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="stock_id",
     *                     type="array",
     *                     @OA\Items(type="string", example="The selected stock id is invalid.")
     *                 ),
     *                 @OA\Property(
     *                     property="price",
     *                     type="array",
     *                     @OA\Items(type="string", example="The price field is required.")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'stock_id' => 'required|exists:stocks,id',
            'price' => 'required|numeric',
        ]);

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

    /**
     * @OA\Get(
     *     path="/v1/stock-prices/{id}",
     *     tags={"Stock Prices"},
     *     summary="Get a specific stock price",
     *     operationId="getStockPrice",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the stock price",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Stock price retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully fetched stock price data with ID 1"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="stock_id", type="integer", example=1),
     *                 @OA\Property(property="price", type="number", format="float", example=150.25),
     *                 @OA\Property(
     *                     property="stock",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="symbol", type="string", example="AAPL"),
     *                     @OA\Property(property="name", type="string", example="Apple Inc.")
     *                 ),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-01T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-01T12:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Stock price not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Could not find stock price data with ID 1")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Put(
     *     path="/v1/stock-prices/{id}",
     *     tags={"Stock Prices"},
     *     summary="Update a specific stock price",
     *     operationId="updateStockPrice",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the stock price",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Cannot update stock price",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You cannot change stock previous price")
     *         )
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        return response()->json([
            'message' => 'You cannot change stock previous price',
        ], 400);
    }

    /**
     * @OA\Delete(
     *     path="/v1/stock-prices/{id}",
     *     tags={"Stock Prices"},
     *     summary="Delete a specific stock price",
     *     operationId="deleteStockPrice",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the stock price",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Cannot delete stock price",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You cannot delete stock previous price")
     *         )
     *     )
     * )
     */
    public function destroy(string $id)
    {
        return response()->json([
            'message' => 'You cannot delete stock previous price',
        ], 400);
    }

    /**
     * @OA\Get(
     *     path="/v1/stocks/{id}/history",
     *     tags={"Stock Prices"},
     *     summary="Get historical price data for a specific stock",
     *     operationId="getStockPriceHistory",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the stock",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Historical stock data retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully rendered stock all historically data"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="stock",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="symbol", type="string", example="AAPL"),
     *                     @OA\Property(property="name", type="string", example="Apple Inc."),
     *                     @OA\Property(
     *                         property="sector",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="technology")
     *                     ),
     *                     @OA\Property(
     *                         property="latest_price",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="price", type="number", format="float", example=150.25),
     *                         @OA\Property(property="date", type="string", format="date", example="2025-04-01")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="historic",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="stock_id", type="integer", example=1),
     *                         @OA\Property(property="price", type="number", format="float", example=150.25),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-01T12:00:00Z"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-01T12:00:00Z")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Stock not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Stock not found"),
     *             @OA\Property(property="data", type="null", example=null)
     *         )
     *     )
     * )
     */
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