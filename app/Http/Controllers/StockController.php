<?php

namespace App\Http\Controllers;

use App\Http\Resources\StockResource;
use App\Models\Stock;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Stocks",
 *     description="Endpoints for managing stocks"
 * )
 */
class StockController extends Controller
{
    /**
     * @OA\Get(
     *     path="/v1/stocks",
     *     tags={"Stocks"},
     *     summary="Get a list of all stocks",
     *     operationId="getStocks",
     *     @OA\Response(
     *         response=200,
     *         description="List of stocks retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully fetched all stocks"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
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
        $stocks = Stock::with(['sector', 'latestPrice'])->get();

        return response()->json([
            'message' => 'Successfully fetched all stocks',
            'data' => StockResource::collection($stocks),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/v1/stocks",
     *     tags={"Stocks"},
     *     summary="Create a new stock",
     *     operationId="createStock",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"symbol", "name", "sector_id"},
     *             @OA\Property(
     *                 property="symbol",
     *                 type="string",
     *                 maxLength=6,
     *                 example="AAPL",
     *                 description="The unique stock symbol (max 6 characters)"
     *             ),
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 example="Apple Inc.",
     *                 description="The name of the stock"
     *             ),
     *             @OA\Property(
     *                 property="sector_id",
     *                 type="integer",
     *                 example=1,
     *                 description="The ID of the sector this stock belongs to"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Stock created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully registered stock"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="symbol", type="string", example="AAPL"),
     *                 @OA\Property(property="name", type="string", example="Apple Inc."),
     *                 @OA\Property(property="sector_id", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-01T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-01T12:00:00Z")
     *             )
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
     *                     property="symbol",
     *                     type="array",
     *                     @OA\Items(type="string", example="The symbol has already been taken.")
     *                 ),
     *                 @OA\Property(
     *                     property="sector_id",
     *                     type="array",
     *                     @OA\Items(type="string", example="The selected sector id is invalid.")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'symbol' => 'required|max:6|unique:stocks,symbol',
            'name' => 'required|string',
            'sector_id' => 'required|integer|exists:sectors,id',
        ]);

        $stock = Stock::create($data);

        return response()->json([
            'message' => 'Successfully registered stock',
            'data' => $stock,
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/v1/stocks/{id}",
     *     tags={"Stocks"},
     *     summary="Get a specific stock",
     *     operationId="getStock",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the stock",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Stock retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully fetched stock data"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="symbol", type="string", example="AAPL"),
     *                 @OA\Property(property="name", type="string", example="Apple Inc."),
     *                 @OA\Property(
     *                     property="sector",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="technology")
     *                 ),
     *                 @OA\Property(
     *                     property="latest_price",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="price", type="number", format="float", example=150.25),
     *                     @OA\Property(property="date", type="string", format="date", example="2025-04-01")
     *                 ),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-01T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-01T12:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Stock not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No Stock found with ID 1")
     *         )
     *     )
     * )
     */
    public function show(string $id)
    {
        $stock = Stock::with(['sector', 'latestPrice'])->find($id);
        if (empty($stock)) {
            return response()->json([
                'message' => 'No Stock found with ID '.$id,
            ], 404);
        }

        return response()->json([
            'message' => 'Successfully fetched stock data',
            'data' => new StockResource($stock),
        ]);
    }

    /**
     * @OA\Put(
     *     path="/v1/stocks/{id}",
     *     tags={"Stocks"},
     *     summary="Update a specific stock",
     *     operationId="updateStock",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the stock",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="symbol",
     *                 type="string",
     *                 maxLength=6,
     *                 example="AAPL",
     *                 description="The unique stock symbol (max 6 characters)"
     *             ),
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 example="Apple Inc.",
     *                 description="The name of the stock"
     *             ),
     *             @OA\Property(
     *                 property="sector_id",
     *                 type="integer",
     *                 example=1,
     *                 description="The ID of the sector this stock belongs to"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Stock updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Stock successfully updated"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="symbol", type="string", example="AAPL"),
     *                 @OA\Property(property="name", type="string", example="Apple Inc."),
     *                 @OA\Property(
     *                     property="sector",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="technology")
     *                 ),
     *                 @OA\Property(
     *                     property="latest_price",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="price", type="number", format="float", example=150.25),
     *                     @OA\Property(property="date", type="string", format="date", example="2025-04-01")
     *                 ),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-01T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-01T12:30:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Stock not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No Stock found with ID 1")
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
     *                     property="symbol",
     *                     type="array",
     *                     @OA\Items(type="string", example="The symbol has already been taken.")
     *                 ),
     *                 @OA\Property(
     *                     property="sector_id",
     *                     type="array",
     *                     @OA\Items(type="string", example="The selected sector id is invalid.")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        $stock = Stock::find($id);

        if (! $stock) {
            return response()->json([
                'message' => 'No Stock found with ID '.$id,
            ], 404);
        }

        $data = $request->validate([
            'symbol' => 'sometimes|string|max:6|unique:stocks,symbol,'.$id,
            'name' => 'sometimes|string',
            'sector_id' => 'sometimes|integer|exists:sectors,id',
        ]);

        $stock->update($data);

        return response()->json([
            'message' => 'Stock successfully updated',
            'data' => new StockResource($stock),
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/v1/stocks/{id}",
     *     tags={"Stocks"},
     *     summary="Delete a specific stock",
     *     operationId="deleteStock",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the stock",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Stock deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully deleted stock with ID 1")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Stock not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No Stock found with ID 1")
     *         )
     *     )
     * )
     */
    public function destroy(string $id)
    {
        $stock = Stock::find($id);

        if (empty($stock)) {
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