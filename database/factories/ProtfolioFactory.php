<?php

namespace Database\Factories;

use App\Models\Stock;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Protfolio>
 */
class ProtfolioFactory extends Factory
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
            'stock_id' => Stock::query()->inRandomOrder()->first()->id ?? Stock::factory()->create()->id,
            'quantity' => $this->faker->numberBetween(1, 10000),
            'average_purchase_price' => $this->faker->numberBetween(1, 10000000),
        ];
    }
}
