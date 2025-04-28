<?php

namespace App\Http\Controllers;

use App\Http\Resources\PortfolioResource;
use App\Models\Portfolio;

/**
 * @OA\Tag(
 *     name="Portfolio",
 *     description="Operations related to Portfolio"
 * )
 */

/**
 * @OA\Schema(
 *     schema="Portfolio",
 *     type="object",
 *     title="Portfolio",
 *     required={"user_id"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=5),
 *     @OA\Property(property="amount", type="number", format="float", example=50000.75),
 *     @OA\Property(
 *         property="holdings",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Holding")
 *     ),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-26T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-26T10:00:00Z"),
 * )
 */
class PortfolioController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/portfolios",
     *     summary="Get the authenticated user's portfolio",
     *     description="Returns the authenticated user's portfolio, including holdings and their latest stock prices.",
     *     tags={"Portfolio"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched portfolio data",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully fetched all portfolios data"),
     *             @OA\Property(property="data", ref="#/components/schemas/Portfolio")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     * )
     */
    public function __invoke()
    {
        $user = auth()->user();
        $portfolios = $user->portfolio()
            ->with(['holdings.stock.latestPrice'])
            ->first();

        return response()->json([
            'message' => 'Successfully fetched all portfolios data',
            'data' => new PortfolioResource($portfolios),
        ]);
    }
}
