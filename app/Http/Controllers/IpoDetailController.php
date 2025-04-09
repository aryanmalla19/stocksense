<?php

namespace App\Http\Controllers;

use App\Http\Requests\IPODetails\UpdateIpoDetailRequest;
use App\Http\Requests\StoreIpoDetailRequest;
use App\Http\Resources\IpoDetailResource;
use App\Models\IpoDetail;

class IpoDetailController extends Controller
{
    public function index()
    {
        // Eager load the 'stock' and 'sector' relationships
        $ipoDetails = IpoDetail::with(['stock.sector', 'stock.latestPrice'])->get();

        return response()->json([
            'message' => 'Successfully fetched all ipo details',
            'data' => IpoDetailResource::collection($ipoDetails),
        ]);
    }

    public function show($id)
    {
        // Eager load the 'stock' and 'sector' relationships
        $ipoDetail = IpoDetail::with(['stock.sector', 'stock.latestPrice'])->find($id);

        if (! $ipoDetail) {
            return response()->json([
                'message' => 'IPO detail not found for '.$id,
            ], 404);
        }

        return response()->json([
            'message' => 'Successfully fetched ipo details',
            'data' => new IpoDetailResource($ipoDetail),
        ]);
    }

    public function store(StoreIpoDetailRequest $request)
    {
        $ipoDetail = IpoDetail::create($request->validated());

        return response()->json([
            'message' => 'Successfully created new IPO detail',
            'data' => new IpoDetailResource($ipoDetail),
        ]);
    }

    public function update(UpdateIpoDetailRequest $request, $id)
    {
        $ipoDetail = IpoDetail::find($id);

        if (! $ipoDetail) {
            return response()->json([
                'message' => 'IPO detail not found for ID: '.$id,
            ], 404);
        }

        $ipoDetail->update($request->validated());

        return response()->json([
            'message' => 'Successfully updated IPO detail',
            'data' => new IpoDetailResource($ipoDetail),
        ]);
    }

    public function destroy($id)
    {
        $ipoDetail = IpoDetail::find($id);

        if (! $ipoDetail) {
            return response()->json([
                'message' => 'Could not find IPO detail with ID: '.$id,
            ], 404);
        }

        $ipoDetail->delete();

        return response()->json([
            'message' => 'Successfully deleted IPO detail with ID: '.$id,
        ]);
    }
}
