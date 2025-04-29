<?php

namespace App\Http\Controllers;

use App\Http\Requests\Stock\StoreStockRequest;
use App\Http\Requests\Stock\UpdateStockRequest;
use App\Http\Resources\StockResource;
use App\Models\Stock;

/**
 * @OA\Tag(
 *      name="Stocks",
 *      description="Operations related to stocks"
 * )
 */

 /**
 * @OA\Schema(
 *     schema="Stock",
 *     type="object",
 *     title="Stock",
 *     required={"id", "name", "price"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Apple Inc."),
 *     @OA\Property(property="price", type="number", format="float", example=178.34)
 * )
 */
class StockController extends Controller
{
     /**
     * @OA\Get(
     *     path="/api/v1/stocks",
     *     summary="Fetch a list of stocks",
     *     tags={"Stocks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="symbol",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="column",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="direction",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched all stocks",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Stock")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Stocks not found"
     *     )
     * )
     */
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

     /**
     * @OA\Post(
     *     path="/api/v1/stocks",
     *     summary="Create a new stock",
     *     tags={"Stocks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Stock")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successfully registered stock",
     *         @OA\JsonContent(ref="#/components/schemas/Stock")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed"
     *     )
     * )
     */
    public function store(StoreStockRequest $request)
    {
        $stock = Stock::create($request->validated());

        return response()->json([
            'message' => 'Successfully registered stock',
            'data' => new StockResource($stock->load('sector')),
        ], 201);
    }

     /**
     * @OA\Get(
     *     path="/api/v1/stocks/{id}",
     *     summary="Fetch a single stock by ID",
     *     tags={"Stocks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched stock data",
     *         @OA\JsonContent(ref="#/components/schemas/Stock")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Stock not found"
     *     )
     * )
     */
    public function show(string $id)
    {
        $stock = Stock::with(['sector', 'latestPrice'])
            ->listed()
            ->find($id);

        if (! $stock) {
            return response()->json([
                'message' => 'No listed stock found with ID '.$id,
            ], 404);
        }

        return response()->json([
            'message' => 'Successfully fetched stock data',
            'data' => new StockResource($stock),
        ]);
    }

     /**
     * @OA\Put(
     *     path="/api/v1/stocks/{id}",
     *     summary="Update an existing stock",
     *     tags={"Stocks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Stock")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Stock successfully updated",
     *         @OA\JsonContent(ref="#/components/schemas/Stock")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Stock not found"
     *     )
     * )
     */
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

     /**
     * @OA\Delete(
     *     path="/api/v1/stocks/{id}",
     *     summary="Delete a stock by ID",
     *     tags={"Stocks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully deleted stock"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Stock not found"
     *     )
     * )
     */
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
