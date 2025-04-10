<?php

namespace Database\Factories;

use App\Models\Holding;
use App\Models\Portfolio;
use App\Models\Stock;
use Illuminate\Database\Eloquent\Factories\Factory;

class HoldingFactory extends Factory
{
    protected $model = Holding::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $portfolio = Portfolio::inRandomOrder()->first() ?? Portfolio::factory()->create();
        $stock = Stock::inRandomOrder()->first() ?? Stock::factory()->create();
        return [
            'portfolio_id' => $portfolio->id,
            'stock_id' => $stock->id,
            'quantity' => $this->faker->numberBetween(10, 150),
            'average_price' => $this->faker->randomFloat(2, 1000, 15000),
            'created_at' => $this->faker->dateTimeThisYear(),
            'updated_at' => $this->faker->dateTimeThisMonth(),
        ];

    }
}
