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
            'two_factor_enabled' => $this->two_factor_enabled,
            'theme' => $this->whenLoaded('setting', fn () => $this->setting->mode),
            'notification_enabled' => $this->whenLoaded('setting', fn() => $this->setting->notification_enabled),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
