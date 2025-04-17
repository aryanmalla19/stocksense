<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PortfolioResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'amount' => $this->amount,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ],
            'holdings' => $this->holdings->map(function ($holding) {
                return [
                    'id' => $holding->id,
                    'stock_id' => $holding->stock_id,
                    'quantity' => $holding->quantity,
                    'average_price' => $holding->average_price,
                    'value' => $holding->quantity * $holding->average_price,
                ];
            })->toArray(),
        ];
    }
}