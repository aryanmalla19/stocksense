<?php

namespace App\Http\Controllers;

use App\Http\Resources\HoldingResource;
use App\Models\Holding;
use Illuminate\Http\JsonResponse;
use Illuminate\Auth\Access\AuthorizationException;

class HoldingController extends Controller
{

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
