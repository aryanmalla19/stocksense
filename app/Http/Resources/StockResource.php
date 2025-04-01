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
        $latestPrice = $this->whenLoaded('latestPrice', fn() => $this->latestPrice, null);

        return [
            'id' => $this->id,
            'symbol' => $this->symbol,
            'company_name' => $this->company_name,
            'sector_id' => $this->sector_id,
            'sector' => $this->whenLoaded('sector', fn() => $this->sector->name, null),
            'open_price' => $latestPrice ? $latestPrice->open_price : null,
            'close_price' => $latestPrice ? $latestPrice->close_price : null,
            'high_price' => $latestPrice ? $latestPrice->high_price : null,
            'low_price' => $latestPrice ? $latestPrice->low_price : null,
        ];
    }
}
