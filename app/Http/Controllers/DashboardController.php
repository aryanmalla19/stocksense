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
            ->where('type', 'BUY')
            ->sum('price');

        $currentHoldings = $user->portfolio->holdings->sum('amount');

        return response()->json([
            'current_amount' => $user->portfolio->amount,
            'total_investment' => $totalInvestment,
            'current_holdings' => $currentHoldings,
        ]);
    }
}
