<?php

namespace Database\Factories;

use App\Models\Stock;
use App\Models\StockPrice;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockPriceFactory extends Factory
{
    protected $model = StockPrice::class;

    private static $previousClose = null;
    private static $currentDate = null;

    public function definition(): array
    {
        if (is_null(self::$currentDate)) {
            self::$currentDate = now()->subMonths(3); // Start 3 months ago
        } else {
            self::$currentDate = self::$currentDate->addDay(); // Move to next day
        }

        if (is_null(self::$previousClose)) {
            self::$previousClose = $this->faker->randomFloat(2, 100, 500);
        }

        $openPrice = self::$previousClose;
        $dailyChange = $this->faker->randomFloat(2, -5, 5);
        $closePrice = round($openPrice + $dailyChange, 2);

        $highPrice = max($openPrice, $closePrice) + $this->faker->randomFloat(2, 0, 3);
        $lowPrice = min($openPrice, $closePrice) - $this->faker->randomFloat(2, 0, 3);

        $currentPrice = round($this->faker->randomFloat(2, $lowPrice, $highPrice), 2);

        self::$previousClose = $closePrice;

        return [
            'stock_id' => Stock::inRandomOrder()->first()?->id ?? Stock::factory()->create()->id,
            'open_price' => round($openPrice, 2),
            'close_price' => $closePrice,
            'high_price' => round($highPrice, 2),
            'low_price' => round($lowPrice, 2),
            'current_price' => $currentPrice,
            'volume' => $this->faker->numberBetween(1000, 1000000),
            'date' => self::$currentDate->format('Y-m-d'),
        ];
    }
}
