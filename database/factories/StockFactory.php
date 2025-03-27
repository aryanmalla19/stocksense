<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Stock>
 */
class StockFactory extends Factory
{
   protected $model = Sector::class;
    public function definition(): array
    {
        return [
            'symbol' => $this->faker->unique()->lexify('????'), // e.g., ADBL, AHPC
            'name' => $this->faker->company . ' Limited',
            'sector_id' => Sector::factory(),     
        ];
    }
}
