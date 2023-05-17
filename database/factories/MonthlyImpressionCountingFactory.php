<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MonthlyImpressionCountingFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $year = $this->faker->year();
        $month = $this->faker->month();
        $dealerId = $this->faker->unique()->numberBetween(1, 1000);

        return [
            'year' => $year,
            'month' => $month,
            'dealer_id' => $dealerId,
            'impressions_count' => $this->faker->numberBetween(1, 10000),
            'views_count' => $this->faker->numberBetween(1, 10000),
            'zip_file_path' => sprintf('%d/%02d/dealer-id-%d.csv.gz', $year, $month, $dealerId),
        ];
    }
}
