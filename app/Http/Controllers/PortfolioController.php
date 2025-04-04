<?php

namespace App\Http\Controllers;

use App\Http\Resources\PortfolioResource;
use App\Models\Portfolio;
use App\Models\User;
use Illuminate\Http\Request;

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
     *     @OA\Response(
     *         response=200,
     *         description="List of portfolios retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="My Stock Portfolio"),
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
     *     )
     * )
     */
    public function index()
    {
        $portfolios = Portfolio::with(['user', 'holdings'])->get();
        return response()->json([
            'message' => 'Successfully fetched all portfolios data',
            'data' => PortfolioResource::collection($portfolios),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/v1/portfolios",
     *     tags={"Portfolios"},
     *     summary="Create a new portfolio",
     *     operationId="createPortfolio",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 example="My Stock Portfolio",
     *                 description="The name of the portfolio"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Portfolio created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="My Stock Portfolio"),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-01T12:00:00Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-01T12:00:00Z")
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
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="The name field is required.")
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
     *     path="/v1/portfolios/{id}",
     *     tags={"Portfolios"},
     *     summary="Get a specific portfolio",
     *     operationId="getPortfolio",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the portfolio",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Portfolio retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="My Stock Portfolio"),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-01T12:00:00Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-01T12:00:00Z")
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
     *         description="Portfolio not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Portfolio not found")
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
     *     path="/v1/portfolios/{id}",
     *     tags={"Portfolios"},
     *     summary="Update a specific portfolio",
     *     operationId="updatePortfolio",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the portfolio",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 example="Updated Portfolio",
     *                 description="The updated name of the portfolio"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Portfolio updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Updated Portfolio"),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-01T12:00:00Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-01T12:30:00Z")
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
     *         description="Portfolio not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Portfolio not found")
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
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="The name field is required.")
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
     *     path="/v1/portfolios/{id}",
     *     tags={"Portfolios"},
     *     summary="Delete a specific portfolio",
     *     operationId="deletePortfolio",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the portfolio",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Portfolio deleted successfully",
     *         @OA\JsonContent()
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
     *         description="Portfolio not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Portfolio not found")
     *         )
     *     )
     * )
     */
    public function destroy(string $id)
    {
        //
    }
}
