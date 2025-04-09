<?php

namespace App\Http\Resources;

use Carbon\Carbon;
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
            'issue_price' => $this->issue_price,
            'total_shares' => $this->total_shares,
            'open_date' => $this->open_date?->format('Y-m-d'),
            'close_date' => $this->close_date?->format('Y-m-d'),
            'listing_date' => $this->listing_date?->format('Y-m-d'),
            'ipo_status' => $this->ipo_status,
            'status_label' => ucfirst($this->ipo_status),

            'days_until_open' => Carbon::now()->diffInDays(Carbon::parse($this->open_date), false),
            // : It will show how many days are left until the IPO opens (or how many days since it opened if the IPO is in the past).
            'stock_id' => $this->stock_id,
            'stock' => new StockResource($this->whenLoaded('stock')),
        ];
    }
}
