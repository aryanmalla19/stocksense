<?php

namespace App\Http\Resources;

use App\Models\IpoApplication;
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
            'days_until_open' => (int) Carbon::now()->diffInDays(Carbon::parse($this->open_date), false),
            'company_name' => $this->whenLoaded('stock')->company_name,
            'stock_id' => $this->stock_id,
            'stock' => new StockResource($this->whenLoaded('stock')),
            'applications' => IpoApplicationResource::collection($this->whenLoaded('applications')),
        ];
    }
}
