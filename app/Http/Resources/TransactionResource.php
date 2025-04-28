<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'type' => $this->type,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'total_price' => number_format($this->price * $this->quantity + $this->transaction_fee, 2),
            'transaction_fee' => $this->transaction_fee,
            'stock_id' => $this->stock_id,
            'symbol' => $this->stock ? $this->stock->symbol : null,
            'company_name' => $this->stock ? $this->stock->company_name : null,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
