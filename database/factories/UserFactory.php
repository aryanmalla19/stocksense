<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => $this->faker->optional(0.7)->dateTimeThisYear(), // 70% verified
            'password' => Hash::make('password'), // Default password
            'remember_token' => Str::random(10),
            'is_active' => $this->faker->boolean(90), // 90% active
            'role' => $this->faker->randomElement(['admin', 'user']),
            'created_at' => $this->faker->dateTimeThisYear(),
            'updated_at' => $this->faker->dateTimeThisYear(),
        ];
    }

    /**
     * Indicate that the user is an admin.
     *
     * @return static
     */
    public function admin()
    {
        return $this->state([
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the user is inactive.
     *
     * @return static
     */
    public function inactive()
    {
        return $this->state([
            'is_active' => false,
        ]);
    }
}