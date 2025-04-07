<?php

namespace App\Http\Controllers;

use App\Http\Requests\Holding\StoreHoldingRequest;
use App\Http\Requests\Holding\UpdateHoldingRequest;
use App\Http\Resources\HoldingResource;
use App\Models\User;
use App\Models\Holding;
use Illuminate\Http\JsonResponse;

class HoldingController extends Controller
{
    /**
     * Display a listing of the user's holdings.
     */
    public function index(User $user): JsonResponse
    {
        $this->authorize('viewAny', [Holding::class, $user]);

        $portfolio = $this->ensurePortfolioExists($user);

        return $this->successResponse(
            'Successfully fetched user holdings',
            HoldingResource::collection($portfolio->holdings)
        );
    }

    /**
     * Store a newly created holding for a user's portfolio.
     */
    public function store(StoreHoldingRequest $request, User $user): JsonResponse
    {
        $this->authorize('create', [Holding::class, $user]);

        $portfolio = $this->ensurePortfolioExists($user);

        $holding = $portfolio->holdings()->create($request->validated());

        return $this->successResponse(
            'Holding created successfully',
            new HoldingResource($holding),
            201
        );
    }

    /**
     * Display the specified holding.
     */
    public function show(User $user, Holding $holding): JsonResponse
    {
        $this->ensureHoldingBelongsToUser($user, $holding);
        $this->authorize('view', $holding);

        return $this->successResponse(
            'Holding details fetched successfully',
            new HoldingResource($holding)
        );
    }

    /**
     * Update the specified holding.
     */
    public function update(UpdateHoldingRequest $request, User $user, Holding $holding): JsonResponse
    {
        $this->ensureHoldingBelongsToUser($user, $holding);
        $this->authorize('update', $holding);

        $holding->update($request->validated());

        return $this->successResponse(
            'Holding updated successfully',
            new HoldingResource($holding)
        );
    }

    /**
     * Remove the specified holding.
     */
    public function destroy(User $user, Holding $holding): JsonResponse
    {
        $this->ensureHoldingBelongsToUser($user, $holding);
        $this->authorize('delete', $holding);

        $holding->delete();

        return $this->successResponse('Holding deleted successfully');
    }

    /**
     * Ensure the user has a portfolio, or return a 404 response.
     */
    private function ensurePortfolioExists(User $user): mixed
    {
        if (!$user->portfolio) {
            abort(404, 'Portfolio not found');
        }

        return $user->portfolio;
    }

    /**
     * Ensure the holding belongs to the user's portfolio, or return a 404 response.
     */
    private function ensureHoldingBelongsToUser(User $user, Holding $holding): void
    {
        if (!$user->portfolio || !$user->portfolio->holdings()->where('id', $holding->id)->exists()) {
            abort(404, 'Holding not found');
        }
    }

    /**
     * Format a successful JSON response.
     */
    private function successResponse(string $message, $data = null, int $status = 200): JsonResponse
    {
        $response = ['message' => $message];
        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $status);
    }
}