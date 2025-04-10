<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WatchListResource extends JsonResource
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
            'user_id' => $this->user_id,
            'symbol' => $this->stock ? $this->stock->symbol : null,
            'company_name' => $this->stock ? $this->stock->company_name : null,
        ];
    }
}
