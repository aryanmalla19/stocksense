<?php

namespace Database\Factories;

use App\Enums\IpoDetailStatus;
use App\Models\IpoDetail;
use App\Models\Stock;
use Illuminate\Database\Eloquent\Factories\Factory;

class IpoDetailFactory extends Factory
{
    protected $model = IpoDetail::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $openDate = $this->faker->dateTimeBetween('-1 month', 'now');
        $closeDate = $this->faker->dateTimeBetween($openDate, '+1 month');
        $listingDate = $this->faker->dateTimeBetween($closeDate, '+2 months');

        return [
            'stock_id' => Stock::inRandomOrder()->first()?->id ?? Stock::factory()->create()->id,
            'issue_price' => 100,
            'total_shares' => $this->faker->numberBetween(1000, 100000),
            'open_date' => $openDate,
            'close_date' => $closeDate,
            'listing_date' => $listingDate,
            'ipo_status' => $this->faker->randomElement(array_column(IpoDetailStatus::cases(), 'value')),
            'created_at' => $this->faker->dateTimeThisYear(),
            'updated_at' => $this->faker->dateTimeThisYear(),
        ];
    }

    /**
     * Indicate that the IPO is opened.
     *
     * @return static
     */
    public function opened()
    {
        return $this->state(['ipo_status' => IpoDetailStatus::Opened]);
    }
}
