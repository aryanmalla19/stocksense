<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sector>
 */
class SectorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement([
                'banking',
                'hydropower',
                'life Insurance',
                'non-life Insurance',
                'health',
                'manufacturing',
                'hotel',
                'trading',
                'microfinance',
                'finance',
                'investment',
                'others',
            ]),
        ];
    }
}
