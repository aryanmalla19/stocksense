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
        $latestPrice = $this->whenLoaded('latestPrice', fn () => $this->latestPrice, null);
        $sector = $this->whenLoaded('sector', fn () => $this->sector, null);

        return [
            'id' => $this->id,
            'symbol' => $this->symbol,
            'company_name' => $this->company_name,
            'sector_id' => $this->sector_id,
            'sector' => $sector ? $sector->name : null,
            'is_listed' => $this->is_listed,
            'sector' => $this->whenLoaded('sector', fn () => $this->sector->name, null),
            'is_watchlist' => $user->watchlists->contains('stock_id', $this->id),
            'prices' => $this->whenLoaded('prices'),
            //Only include price fields if the stock is listed
            'open_price' => $this->is_listed && $latestPrice ? $latestPrice->open_price : null,
            'close_price' => $this->is_listed && $latestPrice ? $latestPrice->close_price : null,
            'high_price' => $this->is_listed && $latestPrice ? $latestPrice->high_price : null,
            'low_price' => $this->is_listed && $latestPrice ? $latestPrice->low_price : null,
            'current_price' => $this->is_listed && $latestPrice ? $latestPrice->current_price : null,

        ];
    }
}   