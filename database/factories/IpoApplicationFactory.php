<?php

namespace Database\Factories;

use App\Models\IpoDetail;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\IpoApplication>
 */
class IpoApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::query()->inRandomOrder()->first()->id ?? User::factory()->create()->id,
            'ipo_id' => IpoDetail::query()->inRandomOrder()->first()->id ?? IpoDetail::factory()->create()->id,
            'applied_shares' => 10,
            'status' => 'pending',
        ];
    }
}
