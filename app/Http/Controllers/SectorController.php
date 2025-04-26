<?php

namespace App\Http\Controllers;

use App\Http\Requests\Sector\StoreSectorRequest;
use App\Http\Requests\Sector\UpdateSectorRequest;
use App\Http\Resources\SectorResource;
use App\Models\Sector;

class SectorController extends Controller
{
    public function index()
    {
        $sectors = Sector::all();

        return SectorResource::collection($sectors);
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

    public function show(Sector $sector)
    {
        return new SectorResource($sector->load('stocks.latestPrice'));
    }

    public function update(UpdateSectorRequest $request, Sector $sector)
    {
        $sector->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully updated sector with ID '.$sector->id,
        ]);
    }

    public function destroy(Sector $sector)
    {
        $sector->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully deleted sector with ID '.$sector->id,
        ]);
    }

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
