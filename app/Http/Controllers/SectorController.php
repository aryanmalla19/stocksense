<?php

namespace App\Http\Controllers;

use App\Http\Requests\Sector\StoreSectorRequest;
use App\Http\Requests\Sector\UpdateSectorRequest;
use App\Http\Resources\SectorResource;
use App\Models\Sector;

/**
 * @OA\Tag(
 *     name="Sector",
 *     description="Operations related to stock Sector"
 * )
 */

/**
 * @OA\Schema(
 *     schema="Sector",
 *     title="Sector",
 *     description="Sector model",
 *     type="object",
 *     required={"name"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Banking"),
 * )
 */
class SectorController extends Controller
{
     /**
     * @OA\Get(
     *     path="/api/v1/sectors",
     *     summary="Get all sectors",
     *     description="Returns a list of all sectors.",
     *     tags={"Sector"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of sectors",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Sector")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $sectors = Sector::all();

        return SectorResource::collection($sectors);
    }

     /**
     * @OA\Post(
     *     path="/api/v1/sectors",
     *     summary="Create a new sector",
     *     description="Creates a new sector with a unique and valid name.",
     *     tags={"Sector"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"name"},
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 description="Name of the sector (must be from predefined values)",
     *                 example="Banking",
     *                 enum={"Banking","Hydropower","Life Insurance","Non-life Insurance","Health","Manufacturing","Hotel","Trading","Microfinance","Finance","Investment","Others"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Sector created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Successfully created sector"),
     *             @OA\Property(property="data", ref="#/components/schemas/Sector")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     * )
     */
    public function store(StoreSectorRequest $request)
    {
        // Validate and create sector
        $sector = Sector::create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully created sector',
            'data' => new SectorResource($sector),
        ]);
    }

     /**
     * @OA\Get(
     *     path="/api/v1/sectors/{id}",
     *     summary="Get a sector",
     *     description="Returns a specific sector along with its stocks and their latest prices.",
     *     tags={"Sector"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Sector ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sector details",
     *         @OA\JsonContent(ref="#/components/schemas/Sector")
     *     ),
     *     @OA\Response(response=404, description="Sector not found")
     * )
     */
    public function show(Sector $sector)
    {
        return new SectorResource($sector->load('stocks.latestPrice'));
    }

     /**
     * @OA\Put(
     *     path="/api/v1/sectors/{id}",
     *     summary="Update a sector",
     *     description="Updates the name of an existing sector.",
     *     tags={"Sector"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Sector ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"name"},
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 description="Name of the sector (must be from predefined values)",
     *                 example="Health",
     *                 enum={"Banking","Hydropower","Life Insurance","Non-life Insurance","Health","Manufacturing","Hotel","Trading","Microfinance","Finance","Investment","Others"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sector updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Successfully updated sector with ID 1"),
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=404, description="Sector not found")
     * )
     */
    public function update(UpdateSectorRequest $request, Sector $sector)
    {
        $sector->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully updated sector with ID '.$sector->id,
        ]);
    }

     /**
     * @OA\Delete(
     *     path="/api/v1/sectors/{id}",
     *     summary="Delete a sector",
     *     description="Deletes a specific sector by ID.",
     *     tags={"Sector"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Sector ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sector deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Successfully deleted sector with ID 1")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Sector not found")
     * )
     */
    public function destroy(Sector $sector)
    {
        $sector->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully deleted sector with ID '.$sector->id,
        ]);
    }

     /**
     * @OA\Get(
     *     path="/api/v1/sectors/stats",
     *     summary="Get sector statistics",
     *     description="Returns number of stocks available under each sector for charts.",
     *     tags={"Sector"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Sector statistics",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="name", type="string", example="Banking"),
     *                 @OA\Property(property="value", type="integer", example=5),
     *             )
     *         )
     *     )
     * )
     */
    public function stats()
    {
        $sectors = Sector::withCount('stocks')->get();

        $chartData = $sectors->map(function ($sector) {
            return [
                'name' => $sector->name,
                'value' => $sector->stocks_count,
            ];
        });

        return response()->json([
            'data' => $chartData,
        ]);
    }

     /**
     * @OA\Get(
     *     path="/api/v1/sectors/user-stats",
     *     summary="Get user sector statistics",
     *     description="Returns the number of stocks a user holds categorized by sectors for chart visualizations.",
     *     tags={"Sector"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User sector statistics",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="name", type="string", example="Hydropower"),
     *                 @OA\Property(property="value", type="integer", example=3),
     *             )
     *         )
     *     )
     * )
     */
    public function userStats()
    {
        $user = auth()->user();

        $stocks = $user->portfolio->holdings->pluck('stock')->flatten();

        $groupedBySector = $stocks->groupBy(function ($stock) {
            return $stock->sector->name ?? 'Unknown';
        });

        $chartData = $groupedBySector->map(function ($stocks, $sectorName) {
            return [
                'name' => $sectorName,
                'value' => $stocks->count(),
            ];
        })->values();

        return response()->json([
            'data' => $chartData,
        ]);
    }
}
