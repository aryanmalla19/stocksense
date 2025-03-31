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
            'Banking',
            'Hydropower',
            'Life Insurance',
            'Non-Life Insurance',
            'Health',
            'Manufacturing',
            'Hotel',
            'Trading',
            'Microfinance',
            'Finance',
            'Investment',
            'Others',
        ];

        return [
            'name' => $this->faker->unique()->randomElement($sectors),
        ];
    }
}
