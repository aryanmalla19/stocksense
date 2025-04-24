<?php

namespace App\Http\Controllers;

use App\Http\Resources\PortfolioResource;
use App\Models\Portfolio;
use Illuminate\Http\Request;

class PortfolioController extends Controller
{
    /**
     * Display the authenticated user's portfolio.
     */
    public function __invoke()
    {
        $user = auth()->user();
        $portfolios = $user->portfolio()
            ->with(['holdings.stock.latestPrice'])
            ->first();

        return response()->json([
            'message' => 'Successfully fetched all portfolios data',
            'data' => new PortfolioResource($portfolios),
        ]);
    }
}
