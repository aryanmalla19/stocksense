<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SectorResource extends JsonResource
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
            'name' => $this->name,
            'total_no_of_stocks' => $this->whenLoaded('stocks', function () {
                return $this->stocks->count();
            }),
            'total_price' => $this->whenLoaded('stocks', function () {
                return $this->stocks->sum(function ($stock) {
                    return optional($stock->latestPrice)->current_price ?? 0;
                });
            }),
            'average_price' => $this->whenLoaded('stocks', function () {
                return $this->stocks->average(function ($stock) {
                    return optional($stock->latestPrice)->current_price ?? 0;
                });
            }),
        ];
    }
}
