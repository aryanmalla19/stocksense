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
        $basePrice = $this->faker->randomFloat(2, 100, 1000);

        $openPrice = $basePrice;
        $priceFluctuation = $this->faker->randomFloat(2, -50, 50);
        $closePrice = round($openPrice + $priceFluctuation, 2);

        $highPrice = max($openPrice, $closePrice) + $this->faker->randomFloat(2, 0, 20);
        $lowPrice = min($openPrice, $closePrice) - $this->faker->randomFloat(2, 0, 20);

        $currentPrice = $this->faker->randomFloat(2, $lowPrice, $highPrice);

        return [
            'stock_id' => Stock::inRandomOrder()->first()?->id ?? Stock::factory()->create()->id,
            'open_price' => $openPrice,
            'close_price' => $closePrice,
            'high_price' => round($highPrice, 2),
            'low_price' => round($lowPrice, 2),
            'current_price' => round($currentPrice, 2),
            'volume' => $this->faker->numberBetween(1000, 1000000),
            'date' => $this->faker->dateTimeThisYear(),
        ];
    }
}
