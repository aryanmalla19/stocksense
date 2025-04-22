<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PortfolioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,

            // Total Investment = avg price * quantity
            'investment' => $this->whenLoaded('holdings', function () {
                return $this->holdings->sum(function ($holding) {
                    return $holding->average_price * $holding->quantity;
                });
            }),
            'net_worth' => $this->whenLoaded('holdings', function () {
                return $this->holdings->sum(function ($holding) {
                    return $holding->stock->latestPrice->current_price * $holding->quantity;
                });
            }),
            'gain_loss' => $this->whenLoaded('holdings', function () {
                return $this->holdings->sum(function ($holding) {
                    $investment = $holding->average_price * $holding->quantity;
                    $net = $holding->stock->latestPrice->current_price * $holding->quantity;
                    return $net - $investment;
                });
            }),

        ];
    }
}
