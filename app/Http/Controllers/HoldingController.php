<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Holding;
use Illuminate\Http\Request;
use App\Http\Resources\HoldingResource;

class HoldingController extends Controller
{
    /**
     * Display a listing of the user's holdings.
     */
    public function index($id)
    {
        $user = User::findOrFail($id);
        $this->authorize('viewAny', [Holding::class, $user]);

        if (!$user->portfolio) {
            return response()->json(['message' => 'Portfolio not found'], 404);
        }

        $holdings = $user->portfolio->holdings;

        return response()->json([
            'message' => 'Successfully fetched user holdings',
            'data' => HoldingResource::collection($holdings),
        ]);
    }

    /**
     * Store a newly created holding for a user's portfolio.
     */
    public function store(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $this->authorize('create', [Holding::class, $user]);

        if (!$user->portfolio) {
            return response()->json(['message' => 'Portfolio not found'], 404);
        }

        $validated = $request->validate([
            'average_price' => 'required',
            'quantity' => 'required|numeric|min:1',
            'price' => 'required|numeric|min:0',
            ''
        ]);

        $holding = $user->portfolio->holdings()->create($validated);

        return response()->json([
            'message' => 'Holding created successfully',
            'data' => new HoldingResource($holding),
        ], 201);
    }

    /**
     * Display the specified holding.
     */
    public function show($userId, $holdingId)
    {
        $user = User::findOrFail($userId);
        $holding = $user->portfolio->holdings()->findOrFail($holdingId);

        $this->authorize('view', $holding);

        return response()->json([
            'message' => 'Holding details fetched successfully',
            'data' => new HoldingResource($holding),
        ]);
    }

    /**
     * Update the specified holding.
     */
    public function update(Request $request, $userId, $holdingId)
    {
        $user = User::findOrFail($userId);
        $holding = $user->portfolio->holdings()->findOrFail($holdingId);

        $this->authorize('update', $holding);

        $validated = $request->validate([
            'average_price' => 'sometimes',
            'quantity' => 'sometimes|numeric|min:1',
            'price' => 'sometimes|numeric|min:0',
        ]);

        $holding->update($validated);

        return response()->json([
            'message' => 'Holding updated successfully',
            'data' => new HoldingResource($holding),
        ]);
    }

    /**
     * Remove the specified holding.
     */
    public function destroy($userId, $holdingId)
    {
        $user = User::findOrFail($userId);
        $holding = $user->portfolio->holdings()->findOrFail($holdingId);

        $this->authorize('delete', $holding);

        $holding->delete();

        return response()->json([
            'message' => 'Holding deleted successfully',
        ]);
    }
}
