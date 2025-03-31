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
        return [
            'symbol' => $this->faker->unique()->lexify('???'), // e.g., ABC, XYZ
            'company_name' => $this->faker->company(),
            'sector_id' => Sector::inRandomOrder()->first()->id ?? Sector::factory()->create()->id,
            'description' => $this->faker->optional(0.8)->paragraph(), // 80% chance of description
            'created_at' => $this->faker->dateTimeThisYear(),
            'updated_at' => $this->faker->dateTimeThisYear(),
        ];
    }
}
