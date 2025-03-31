<?php

namespace Database\Factories;

use App\Models\Stock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\IpoDetail>
 */
class IpoDetailFactory extends Factory
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
            'issue_price' => 100,
            'total_shares' => $this->faker->numberBetween(100,10000),
            'open_date' => $this->faker->date(),
            'close_date' => $this->faker->date(),
        ];
    }
}
