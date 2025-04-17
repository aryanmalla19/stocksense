<?php

namespace Database\Factories;

use App\Models\Sector;
use App\Models\Stock;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockFactory extends Factory
{
    protected $model = Stock::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sector = Sector::inRandomOrder()->first() ?? Sector::factory()->create();

        return [
            'symbol' => strtoupper($this->faker->unique()->lexify('?????')),
            'company_name' => $this->faker->company(),
            'sector_id' => $sector->id,
            'description' => $this->faker->optional(0.8)->paragraph(), // 80% chance of description
            'is_listed' => true,
            'created_at' => $this->faker->dateTimeThisYear(),
            'updated_at' => $this->faker->dateTimeThisYear(),
        ];
    }

    public function withNotActive(): Factory
    {
        return $this->state([
            'is_listed' => false,
        ]);
    }
}
