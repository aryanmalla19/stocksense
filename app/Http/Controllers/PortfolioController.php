<?php

namespace App\Http\Controllers;

use App\Http\Resources\PortfolioResource;
use App\Models\Portfolio;


class PortfolioController extends Controller
{
    
    public function index()
    {
        if (auth()->user()->role != 'admin') {
            return response()->json([
                'message' => 'You are not admin',
            ], 403);
        }
        $portfolios = Portfolio::with(['user', 'holdings'])->get();

        return response()->json([
            'message' => 'Successfully fetched all portfolios data',
            'data' => PortfolioResource::collection($portfolios),
        ]);
    }
}
