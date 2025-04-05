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
            'portfolio_id' => Portfolio::inRandomOrder()->first()?->id ?? Portfolio::factory()->create()->id,
            'stock_id' => Stock::inRandomOrder()->first()?->id ?? Stock::factory()->create()->id,
            'quantity' => $this->faker->numberBetween(1, 100),
            'average_price' => $this->faker->randomFloat(2, 100, 10000),
            'created_at' => $this->faker->dateTimeThisYear(),
            'updated_at' => $this->faker->dateTimeThisMonth(),
        ];

    }
}
