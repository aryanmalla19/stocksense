<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Transactions",
 *     description="Endpoints for managing user stock transactions"
 * )
 */
class TransactionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/v1/transactions",
     *     tags={"Transactions"},
     *     summary="Get a list of user transactions",
     *     operationId="getTransactions",
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of transactions retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully fetched all transactions"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="user_id", type="integer", example=1),
     *                     @OA\Property(property="stock_id", type="integer", example=1),
     *                     @OA\Property(property="quantity", type="integer", example=100),
     *                     @OA\Property(property="price", type="number", format="float", example=150.25),
     *                     @OA\Property(property="type", type="string", enum={"buy", "sell"}, example="buy"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-01T12:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-01T12:00:00Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function index()
    {
        //
    }

    /**
     * @OA\Post(
     *     path="/v1/transactions",
     *     tags={"Transactions"},
     *     summary="Create a new transaction",
     *     operationId="createTransaction",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"stock_id", "quantity", "price", "type"},
     *             @OA\Property(
     *                 property="stock_id",
     *                 type="integer",
     *                 example=1,
     *                 description="The ID of the stock being transacted"
     *             ),
     *             @OA\Property(
     *                 property="quantity",
     *                 type="integer",
     *                 example=100,
     *                 description="The number of shares"
     *             ),
     *             @OA\Property(
     *                 property="price",
     *                 type="number",
     *                 format="float",
     *                 example=150.25,
     *                 description="The price per share"
     *             ),
     *             @OA\Property(
     *                 property="type",
     *                 type="string",
     *                 enum={"buy", "sell"},
     *                 example="buy",
     *                 description="The type of transaction"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Transaction created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully created transaction"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="stock_id", type="integer", example=1),
     *                 @OA\Property(property="quantity", type="integer", example=100),
     *                 @OA\Property(property="price", type="number", format="float", example=150.25),
     *                 @OA\Property(property="type", type="string", enum={"buy", "sell"}, example="buy"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-01T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-01T12:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
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
     *                     @OA\Items(type="string", example="The stock id field is required.")
     *                 ),
     *                 @OA\Property(
     *                     property="quantity",
     *                     type="array",
     *                     @OA\Items(type="string", example="The quantity field is required.")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * @OA\Get(
     *     path="/v1/transactions/{id}",
     *     tags={"Transactions"},
     *     summary="Get a specific transaction",
     *     operationId="getTransaction",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the transaction",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transaction retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully fetched transaction"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="stock_id", type="integer", example=1),
     *                 @OA\Property(property="quantity", type="integer", example=100),
     *                 @OA\Property(property="price", type="number", format="float", example=150.25),
     *                 @OA\Property(property="type", type="string", enum={"buy", "sell"}, example="buy"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-01T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-01T12:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Transaction not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Transaction not found")
     *         )
     *     )
     * )
     */
    public function show(string $id)
    {
        //
    }

    /**
     * @OA\Put(
     *     path="/v1/transactions/{id}",
     *     tags={"Transactions"},
     *     summary="Update a specific transaction",
     *     operationId="updateTransaction",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the transaction",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="quantity",
     *                 type="integer",
     *                 example=150,
     *                 description="The updated number of shares"
     *             ),
     *             @OA\Property(
     *                 property="price",
     *                 type="number",
     *                 format="float",
     *                 example=155.75,
     *                 description="The updated price per share"
     *             ),
     *             @OA\Property(
     *                 property="type",
     *                 type="string",
     *                 enum={"buy", "sell"},
     *                 example="sell",
     *                 description="The updated type of transaction"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transaction updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully updated transaction"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="stock_id", type="integer", example=1),
     *                 @OA\Property(property="quantity", type="integer", example=150),
     *                 @OA\Property(property="price", type="number", format="float", example=155.75),
     *                 @OA\Property(property="type", type="string", enum={"buy", "sell"}, example="sell"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-01T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-01T12:30:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Transaction not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Transaction not found")
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
     *                     property="quantity",
     *                     type="array",
     *                     @OA\Items(type="string", example="The quantity must be an integer.")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * @OA\Delete(
     *     path="/v1/transactions/{id}",
     *     tags={"Transactions"},
     *     summary="Delete a specific transaction",
     *     operationId="deleteTransaction",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the transaction",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transaction deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully deleted transaction")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Transaction not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Transaction not found")
     *         )
     *     )
     * )
     */
    public function destroy(string $id)
    {
        //
    }
}