<?php

namespace App\Http\Controllers;

use App\Http\Resources\HoldingResource;
use App\Models\Holding;
use Illuminate\Http\JsonResponse;

class HoldingController extends Controller
{
    /**
     * Display a listing of the user's holdings.
     */
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', [Holding::class, auth()->user()]);

        $portfolio = auth()->user()->portfolio;

        return response()->json([
            'message' => 'Successfully fetched user holdings',
            'data' => HoldingResource::collection($portfolio->holdings),
        ]);
    }

    /**
     * Display the specified holding.
     */
    public function show(Holding $holding): JsonResponse
    {
        $this->authorize('view', $holding);

        return response()->json([
            'message' => 'Holding details fetched successfully',
            'data' => new HoldingResource($holding),
        ]);
    }
}
