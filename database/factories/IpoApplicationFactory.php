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
        $user = User::inRandomOrder()->first() ?? User::factory()->create();
        $ipo = IpoDetail::inRandomOrder()->first() ?? IpoDetail::factory()->create();
        $status = $this->faker->randomElement(['pending', 'allotted', 'not_allotted']);
        $appliedShares = $this->faker->numberBetween(10, 15);
        $allottedShares = $status === 'allotted' ? $this->faker->numberBetween(1, $appliedShares) : null;

        return [
            'user_id' => $user->id,
            'ipo_id' => $ipo->id,
            'applied_shares' => $appliedShares,
            'status' => $status,
            'applied_date' => $this->faker->dateTimeThisMonth(),
            'allotted_shares' => $allottedShares,
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
        return $this->state(function (array $attributes) {
            return [
                'status' => 'allotted',
                'allotted_shares' => $this->faker->numberBetween(1, $attributes['applied_shares']),
            ];
        });
    }
}
