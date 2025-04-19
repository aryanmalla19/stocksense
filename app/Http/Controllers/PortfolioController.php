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
    public function index()
    {
        $user = auth()->user();
        $portfolios = $user->portfolio()
            ->with(['holdings'])
            ->get();

        return response()->json([
            'message' => 'Successfully fetched all portfolios data',
            'data' => $portfolios,
        ]);
    }
}
