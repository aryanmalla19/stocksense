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
        $user = auth()->user();
        $isAdmin = $user?->role == 'admin';

        return [
            'id' => $this->id,
            'issue_price' => $this->issue_price,
            'total_shares' => $this->total_shares,
            'open_date' => $this->open_date?->format('Y-m-d H:i:s'),
            'close_date' => $this->close_date?->format('Y-m-d H:i:s'),
            'listing_date' => $this->listing_date?->format('Y-m-d H:i:s'),
            'ipo_status' => $this->ipo_status,
            'days_until_open' => (int) Carbon::now()->diffInDays(Carbon::parse($this->open_date), false),
            'company_name' => $this->whenLoaded('stock')->company_name ?? null,
            'stock_id' => $this->stock_id,
            'has_applied' => $this->whenLoaded('applications', function () use ($user) {
                return $this->applications->contains('user_id', $user?->id);
            }),
            'stock' => new StockResource($this->whenLoaded('stock')),
            'applications' => $isAdmin ? IpoApplicationResource::collection($this->whenLoaded('applications')) : null,
        ];
    }
}
