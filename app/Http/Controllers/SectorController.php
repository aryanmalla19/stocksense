<?php

namespace App\Http\Controllers;

use App\Http\Resources\SectorResource;
use App\Models\Sector;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Sectors",
 *     description="Endpoints for managing sectors"
 * )
 */
class SectorController extends Controller
{
    /**
     * @OA\Get(
     *     path="/v1/sectors",
     *     tags={"Sectors"},
     *     summary="Get a list of all sectors",
     *     operationId="getSectors",
     *     @OA\Response(
     *         response=200,
     *         description="List of sectors retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully fetched all sectors data"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="banking"),
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
        $sectors = Sector::all();

        return response()->json([
            'message' => 'Successfully fetched all sectors data',
            'data' => SectorResource::collection($sectors),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/v1/sectors",
     *     tags={"Sectors"},
     *     summary="Create a new sector",
     *     operationId="createSector",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 enum={"banking", "hydropower", "life Insurance", "non-life Insurance", "health", "manufacturing", "hotel", "trading", "microfinance", "finance", "investment", "others"},
     *                 example="banking",
     *                 description="The name of the sector"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Sector created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully created sector"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="banking"),
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
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string",
     *                         example="The name field is required."
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|in:banking,hydropower,life Insurance,non-life Insurance,health,manufacturing,hotel,trading,microfinance,finance,investment,others',
        ]);

        $sector = Sector::create($data);

        return response()->json([
            'message' => 'Successfully created sector',
            'data' => new SectorResource($sector),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/v1/sectors/{id}",
     *     tags={"Sectors"},
     *     summary="Get a specific sector",
     *     operationId="getSector",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the sector",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sector retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully fetched sector data"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="banking"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-01T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-01T12:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Sector not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Could not find sector with ID 1")
     *         )
     *     )
     * )
     */
    public function show(string $id)
    {
        $sector = Sector::find($id);
        if (! $sector) {
            return response()->json([
                'message' => 'Could not find sector with ID '.$id,
            ], 404);
        }

        return response()->json([
            'message' => 'Successfully fetched sector data',
            'data' => new SectorResource($sector),
        ]);
    }

    /**
     * @OA\Put(
     *     path="/v1/sectors/{id}",
     *     tags={"Sectors"},
     *     summary="Update a specific sector",
     *     operationId="updateSector",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the sector",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 enum={"banking", "hydropower", "life Insurance", "non-life Insurance", "health", "manufacturing", "hotel", "trading", "microfinance", "finance", "investment", "others"},
     *                 example="hydropower",
     *                 description="The updated name of the sector"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sector updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully updated sector with ID 1"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="hydropower"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-01T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-01T12:30:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Sector not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Could not find sector with ID 1")
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
     *                     @OA\Items(
     *                         type="string",
     *                         example="The name field is required."
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        $sector = Sector::find($id);
        if (empty($sector)) {
            return response()->json([
                'message' => 'Could not find sector with ID '.$id,
            ], 404);
        }

        $data = $request->validate([
            'name' => 'required|in:banking,hydropower,life Insurance,non-life Insurance,health,manufacturing,hotel,trading,microfinance,finance,investment,others',
        ]);

        $sector->update($data);

        return response()->json([
            'message' => 'Successfully updated sector with ID '.$id,
            'data' => new SectorResource($sector),
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/v1/sectors/{id}",
     *     tags={"Sectors"},
     *     summary="Delete a specific sector",
     *     operationId="deleteSector",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the sector",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sector deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully deleted sector with ID 1")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Sector not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Could not find sector with ID 1")
     *         )
     *     )
     * )
     */
    public function destroy(string $id)
    {
        $sector = Sector::find($id);
        if (empty($sector)) {
            return response()->json([
                'message' => 'Could not find sector with ID '.$id,
            ], 404);
        }

        $sector->delete();

        return response()->json([
            'message' => 'Successfully deleted sector with ID '.$id,
        ]);
    }
}