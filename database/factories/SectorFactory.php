<?php

namespace Database\Factories;

use App\Models\Sector;
use Illuminate\Database\Eloquent\Factories\Factory;

class SectorFactory extends Factory
{
    protected $model = Sector::class;

    public function definition(): array
    {
        static $sectors = [
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
        ];

        return [
            'name' => $this->faker->unique()->randomElement($sectors),
        ];
    }
}
