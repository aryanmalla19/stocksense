<?php

namespace App\Http\Controllers;

use App\Http\Resources\PortfolioResource;
use App\Models\Portfolio;

class PortfolioController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $portfolios = $user->portfolio()->with(['users', 'holdings'])->get();

        return response()->json([
            'message' => 'Successfully fetched all portfolios data',
            'data' => PortfolioResource::collection($portfolios),
        ]);
    }
}
