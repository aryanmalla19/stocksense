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
    public function index(Request $request)
    {
        $user = auth()->user();
        $portfolios = Portfolio::where('user_id', $user->id)
            ->with(['user', 'holdings.stock'])
            ->get();

        return response()->json([
            'message' => 'Successfully fetched all portfolios data',
            'data' => PortfolioResource::collection($portfolios),
        ]);
    }
}