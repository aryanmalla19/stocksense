<?php

namespace Database\Factories;

use App\Models\Portfolio;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PortfolioFactory extends Factory
{
    protected $model = Portfolio::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::inRandomOrder()->first() ?? User::factory()->create();

        return [
            'user_id' => $user->id,
            'created_at' => $this->faker->dateTimeThisYear(),
            'updated_at' => $this->faker->dateTimeThisYear(),
        ];
    }
}
