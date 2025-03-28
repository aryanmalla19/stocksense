<?php

namespace Database\Factories;

use App\Models\Stock;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement(['buy', 'sell']),
            'quantity' => $this->faker->numberBetween(1, 1000), // Ensuring quantity is at least 1
            'price' => $this->faker->numberBetween(100, 1000000), // More realistic stock price range
            'user_id' => User::query()->inRandomOrder()->first()->id ?? User::factory()->create()->id,
            'stock_id' => Stock::query()->inRandomOrder()->first()->id ?? Stock::factory()->create()->id,
        ];
    }
}
