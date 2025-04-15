<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HoldingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'stock_id' => $this->stock_id,
            'quantity' => $this->quantity,
            'average_price' => $this->average_price,
            'stock' => $this->whenLoaded('stock'),
        ];
    }
}
