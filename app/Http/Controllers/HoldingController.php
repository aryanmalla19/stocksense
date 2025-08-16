<?php

namespace App\Http\Controllers;

use App\Http\Resources\HoldingResource;
use App\Models\Holding;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Holdings",
 *     description="Operations related to user holdings"
 * )
 */

/**
 * @OA\Schema(
 *     schema="Holding",
 *     type="object",
 *     title="Holding",
 *     required={"id", "quantity", "stock_id", "portfolio_id"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="quantity", type="number", format="float", example=10.5),
 *     @OA\Property(property="stock_id", type="integer", example=2),
 *     @OA\Property(property="portfolio_id", type="integer", example=1),
 *     @OA\Property(
 *         property="stock",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=2),
 *         @OA\Property(property="symbol", type="string", example="AAPL"),
 *         @OA\Property(property="sector", type="string", example="Technology")
 *     )
 * )
 */
class HoldingController extends Controller
{
     /**
     * @OA\Get(
     *     path="/api/v1/holdings",
     *     summary="Fetch all holdings of authenticated user",
     *     tags={"Holdings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched user holdings",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Holding")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', [Holding::class, auth()->user()]);

        $portfolio = auth()->user()->portfolio;
        $holdings = $portfolio ? $portfolio->holdings->load('stock.sector') : collect([]);

        return response()->json([
            'message' => 'Successfully fetched user holdings',
            'data' => HoldingResource::collection($holdings),
        ]);
    }

     /**
     * @OA\Get(
     *     path="/api/v1/holdings/{holding}",
     *     summary="Fetch a specific holding by ID",
     *     tags={"Holdings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="holding",
     *         in="path",
     *         description="ID of the holding to fetch",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Holding details fetched successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Holding")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Holding not found"
     *     )
     * )
     */
    public function show(Holding $holding): JsonResponse
    {
        try {
            $this->authorize('view', $holding);
        } catch (AuthorizationException $e) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $holding->load('stock.sector');

        return response()->json([
            'message' => 'Holding details fetched successfully',
            'data' => new HoldingResource($holding),
        ]);
    }
}
