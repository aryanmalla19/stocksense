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
            'name' => $this->faker->randomElement([
                'banking',
                'hydropower',
                'insurance',
                'health',
                'manufacturing',
                'hotels',
                'trading',
                'others',
            ]),
        ];
    }
}
