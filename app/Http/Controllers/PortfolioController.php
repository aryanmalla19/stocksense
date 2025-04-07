<?php

namespace App\Http\Controllers;

use App\Http\Resources\PortfolioResource;
use App\Models\Portfolio;
use App\Http\Models\User;
use Illuminate\Http\Request;


class PortfolioController extends Controller
{
    
    public function index()
    {
        $portfolios = Portfolio::with(['user', 'holdings'])->get();
        return response()->json([
            'message' => 'Successfully fetched all portfolios data',
            'data' => PortfolioResource::collection($portfolios),
        ]);
    }

    
    public function store(Request $request)
    {
        //
    }


    public function show(string $id)
    {
        //
    }


    public function update(Request $request, string $id)
    {
        //
    }


    public function destroy(string $id)
    {
        //
    }
}
