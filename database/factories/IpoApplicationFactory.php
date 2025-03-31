<?php

namespace Database\Factories;

use App\Models\IpoApplication;
use App\Models\IpoDetail;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class IpoApplicationFactory extends Factory
{
    protected $model = IpoApplication::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory()->create()->id,
            'ipo_id' => IpoDetail::inRandomOrder()->first()->id ?? IpoDetail::factory()->create()->id,
            'applied_shares' => $this->faker->numberBetween(10, 100),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'applied_date' => $this->faker->dateTimeThisMonth(),
            'allotted_shares' => $this->faker->numberBetween(0, 50),
            'created_at' => $this->faker->dateTimeThisMonth(),
            'updated_at' => $this->faker->dateTimeThisMonth(),
        ];
    }

    /**
     * Indicate that the application is approved.
     *
     * @return static
     */
    public function approved()
    {
        return $this->state([
            'status' => 'approved',
            'allotted_shares' => fn (array $attributes) => $attributes['applied_shares'],
        ]);
    }
}
