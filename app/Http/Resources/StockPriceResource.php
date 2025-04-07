<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class StockPriceResource extends JsonResource
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
            'stock_id' => $this->stock_id,
            'open_price' => $this->open_price,
            'close_price' => $this->close_price,
            'high_price' => $this->high_price,
            'low_price' => $this->low_price,
            'date' => $this->date,
        ];
    }
}
