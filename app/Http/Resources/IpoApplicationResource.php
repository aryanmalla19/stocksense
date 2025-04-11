<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IpoApplicationResource extends JsonResource
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
            'user_id' => $this->user_id,
            'ipo_id' => $this->ipo_id,
            'applied_shares' => $this->applied_shares,
            'status' => $this->status,
            'applied_date' => $this->applied_date,
            'allotted_shares' => $this->allotted_shares,
        ];
    }
}
