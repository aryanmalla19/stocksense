<?php

namespace Database\Factories;

use App\Models\Stock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockPrice>
 */
class StockPriceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'stock_id' => Stock::query()->inRandomOrder()->first()->id ?? Stock::factory()->create()->id,
            'open_price' => $this->faker->numberBetween(100, 999999),
            'close_price' => $this->faker->numberBetween(100, 999999),
            'high_price' => $this->faker->numberBetween(100, 999999),
            'low_price' => $this->faker->numberBetween(100, 999999),
            'volume' => $this->faker->numberBetween(100, 999999),
        ];
    }
}
