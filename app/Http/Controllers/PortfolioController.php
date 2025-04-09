<?php

namespace App\Http\Controllers;

use App\Http\Resources\PortfolioResource;
use App\Models\Portfolio;

/**
 * @OA\Tag(
 *     name="Portfolios",
 *     description="Endpoints for managing user portfolios"
 * )
 */
class PortfolioController extends Controller
{
    /**
     * @OA\Get(
     *     path="/v1/portfolios",
     *     tags={"Portfolios"},
     *     summary="Get a list of user portfolios",
     *     operationId="getPortfolios",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of portfolios retrieved successfully",
     *
     *         @OA\JsonContent(
     *             type="array",
     *
     *             @OA\Items(
     *
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="My Stock Portfolio"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-01T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-01T12:00:00Z")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function index()
    {
        if (auth()->user()->role != 'admin') {
            return response()->json([
                'message' => 'You are not admin',
            ], 403);
        }
        $portfolios = Portfolio::with(['user', 'holdings'])->get();

        return response()->json([
            'message' => 'Successfully fetched all portfolios data',
            'data' => PortfolioResource::collection($portfolios),
        ]);
    }
}
