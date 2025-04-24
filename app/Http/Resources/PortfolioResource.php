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
            'amount' => (float) $this->amount,
            'investment' => $this->whenLoaded('holdings', function () {
                $sum = 0.0;
                foreach ($this->holdings as $holding) {
                    $sum += $holding->average_price * $holding->quantity;
                }
                return round($sum, 2);
            }, 0.00),
            'net_worth' => $this->whenLoaded('holdings', function () {
                $sum = 0.0;
                foreach ($this->holdings as $holding) {
                    $sum += $holding->stock->latestPrice->current_price * $holding->quantity;
                }
                return round($sum, 2);
            }, 0.00),
            'gain_loss' => $this->whenLoaded('holdings', function () {
                $sum = 0.0;
                foreach ($this->holdings as $holding) {
                    $investment = $holding->average_price * $holding->quantity;
                    $net = $holding->stock->latestPrice->current_price * $holding->quantity;
                    $sum += $net - $investment;
                }
                return round($sum, 2);
            }, 0.00),
        ];
    }
}