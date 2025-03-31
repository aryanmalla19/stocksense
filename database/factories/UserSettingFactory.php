<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserSettingFactory extends Factory
{
    protected $model = UserSetting::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'notification_enabled' => $this->faker->boolean(80), // 80% chance enabled
            'mode' => $this->faker->randomElement(['dark', 'light']),
            'created_at' => $this->faker->dateTimeThisYear(),
            'updated_at' => $this->faker->dateTimeThisYear(),
        ];
    }

    /**
     * Indicate that notifications are disabled.
     *
     * @return static
     */
    public function notificationsDisabled()
    {
        return $this->state(['notification_enabled' => false]);
    }
}