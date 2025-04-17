<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $user = auth()->user();

        $totalInvestment = $user->transactions()
            ->where('type', 'buy') // Use lowercase to match enum
            ->sum('price');

        $portfolio = $user->portfolio;
        $currentAmount = $portfolio ? $portfolio->amount : 0;
        $currentHoldings = $portfolio ? $portfolio->holdings->sum('value') : 0; // Use value attribute

        return response()->json([
            'current_amount' => $currentAmount,
            'total_investment' => $totalInvestment,
            'current_holdings' => $currentHoldings,
        ]);
    }
}