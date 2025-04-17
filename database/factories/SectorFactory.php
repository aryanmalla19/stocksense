<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SectorFactory extends Factory
{
    protected $model = \App\Models\Sector::class;

    public function definition()
    {
        return [
            'name' => $this->faker->randomElement([
                'Banking', 'Hydropower', 'Life Insurance', 'Non-life Insurance',
                'Health', 'Manufacturing', 'Hotel', 'Trading',
                'Microfinance', 'Finance', 'Investment', 'Others'
            ]),
        ];
    }
}