<?php

namespace Database\Factories;

use App\Models\Stock;
use App\Models\StockPrice;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockPriceFactory extends Factory
{
    protected $model = StockPrice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $basePrice = $this->faker->randomFloat(2, 10, 1000); // Base for consistency
        return [
            'stock_id' => Stock::factory(),
            'open_price' => $basePrice,
            'close_price' => $basePrice + $this->faker->randomFloat(2, -50, 50),
            'high_price' => $basePrice + $this->faker->randomFloat(2, 0, 100),
            'low_price' => $basePrice - $this->faker->randomFloat(2, 0, 100),
            'volume' => $this->faker->numberBetween(1000, 1000000),
            'date' => $this->faker->dateTimeThisYear(),
        ];
    }
}