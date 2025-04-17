<?php

namespace App\Http\Controllers;

use App\Http\Requests\Sector\StoreSectorRequest;
use App\Http\Requests\Sector\UpdateSectorRequest;
use App\Http\Resources\SectorResource;
use App\Models\Sector;

class SectorController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth:api');
    // }
    public function index()
{
    $sectors = Sector::paginate(15); // Paginate the results
    $resource = SectorResource::collection($sectors);
    $paginationData = $resource->response()->getData(true);

    return response()->json([
        'status' => 'success',
        'message' => 'Successfully fetched all sectors data',
        'data' => [
            'data' => $paginationData['data'],
            'current_page' => $paginationData['meta']['current_page'],
            'per_page' => $paginationData['meta']['per_page'],
            'total' => $paginationData['meta']['total'],
        ],
    ]);
}

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
     * Display the specified sector.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        // Find sector or fail with a 404 response
        $sector = $this->findSectorOrFail($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully fetched sector data',
            'data' => new SectorResource($sector),
        ]);
    }

    public function update(UpdateSectorRequest $request, string $id)
    {
        // Find sector or fail with a 404 response
        $sector = $this->findSectorOrFail($id);

        // Update the sector with validated data
        $sector->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully updated sector with ID '.$id,
            'data' => new SectorResource($sector),
        ]);
    }

    public function destroy(string $id)
    {
        // Find sector or fail with a 404 response
        $sector = $this->findSectorOrFail($id);

        // Delete the sector
        $sector->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully deleted sector with ID '.$id,
        ]);
    }

    protected function findSectorOrFail(string $id)
    {
        return Sector::findOrFail($id); // Automatically throws 404 if not found
    }
}
