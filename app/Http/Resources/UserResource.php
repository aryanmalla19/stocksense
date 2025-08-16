<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'email' => $this->email,
            'role' => $this->role,
            'is_active' => $this->is_active,
            'phone_number' => $this->phone_number,
            'bio' => $this->bio,
            'profile_image' => $this->profile_image ? asset('/storage/'.$this->profile_image) : asset('/images/default-profile.png'),
            'two_factor_enabled' => $this->two_factor_enabled,
            'theme' => $this->whenLoaded('setting', fn () => $this->setting->mode),
            'notification_enabled' => $this->whenLoaded('setting', fn () => $this->setting->notification_enabled),
            'portfolio' => $this->whenLoaded('portfolio', function () {
                return new PortfolioResource($this->portfolio);
            }),
            'holdings' => $this->whenLoaded('portfolio', function () {
                return HoldingResource::collection($this->portfolio->holdings);
            }),
            'transactions' => $this->whenLoaded('transactions', function () {
                return TransactionResource::collection($this->transactions);
            }),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
