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
        return [
            'portfolio_id' => Portfolio::factory(),
            'stock_id' => Stock::factory(),
            'quantity' => $this->faker->numberBetween(1, 100),
            'average_price' => $this->faker->randomFloat(2, 10, 1000),
            'created_at' => $this->faker->dateTimeThisYear(),
            'updated_at' => $this->faker->dateTimeThisYear(),
        ];
    }
}
