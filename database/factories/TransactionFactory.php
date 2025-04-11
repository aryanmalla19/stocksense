<?php

namespace Database\Factories;

use App\Models\Stock;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory()->create()->id,
            'stock_id' => Stock::inRandomOrder()->first()?->id ?? Stock::factory()->create()->id,
            'type' => $this->faker->randomElement(['buy', 'sell', 'ipo_allotted']),
            'quantity' => $this->faker->numberBetween(10, 100),
            'price' => $this->faker->randomFloat(2, 1000, 100000),
            'transaction_fee' => $this->faker->randomFloat(2, 0.5, 50), // Fee between 0.5 and 50
            'created_at' => $this->faker->dateTimeThisMonth(),
            'updated_at' => $this->faker->dateTimeThisMonth(),
        ];
    }

    /**
     * Indicate that the transaction is a buy.
     *
     * @return static
     */
    public function buy()
    {
        return $this->state(['type' => 'buy']);
    }

    /**
     * Indicate that the transaction is a sell.
     *
     * @return static
     */
    public function sell()
    {
        return $this->state(['type' => 'sell']);
    }

    /**
     * Indicate that the transaction is an IPO allotment.
     *
     * @return static
     */
    public function ipoAllotted()
    {
        return $this->state(['type' => 'ipo_allotted']);
    }
}
