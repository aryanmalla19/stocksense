<?php

namespace Database\Factories;

use App\Models\Sector;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Stock>
 */
class StockFactory extends Factory
{
    public function definition(): array
    {
        return [
            'symbol' => $this->faker->unique()->lexify('????'),
            'company_name' => $this->faker->company.' Limited',
            'sector_id' => Sector::query()->inRandomOrder()->first()->id ?? Sector::factory()->create()->id,
        ];
    }
}
