<?php

namespace Database\Factories;

use App\Models\Sector;
use Illuminate\Database\Eloquent\Factories\Factory;

class SectorFactory extends Factory
{
    protected $model = Sector::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
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
        static $index = 0;

        return [
            'name' => $sectors[$index++ % count($sectors)],
        ];
    }
}
