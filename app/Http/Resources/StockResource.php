<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $latestPrice = $this->whenLoaded('latestPrice');

        return [
            'id' => $this->id,
            'symbol' => $this->symbol,
            'company_name' => $this->company_name,
            'sector_id' => $this->sector_id,
            'sector' => $this->whenLoaded('sector', fn() => $this->sector->name, null),
            'open_price' => $latestPrice?->open_price,
            'close_price' => $latestPrice?->close_price,
            'high_price' => $latestPrice?->high_price,
            'low_price' => $latestPrice?->low_price,
        ];
    }
}
