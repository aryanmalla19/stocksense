<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockWithPriceResource extends JsonResource
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
            'symbol' => $this->symbol,
            'name' => $this->name,
            'sector_id' => $this->sector_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'prices' => StockPriceResource::collection($this->whenLoaded('prices')),
        ];
    }
}
