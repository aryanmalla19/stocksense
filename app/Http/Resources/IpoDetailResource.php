<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IpoDetailResource extends JsonResource
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
            'issue_price' => $this->issue_price,
            'total_shares' => $this->total_shares,
            'open_date' => $this->open_date,
            'close_date' => $this->close_date,
            'listing_date' => $this->listing_date,
            'ipo_status' => $this->ipo_status
        ];
    }
}
