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
        return [
            'year' => $this->faker->year(),
            'month' => $this->faker->month(),
            'dealer_id' => $this->faker->numberBetween(1, 1000),
            'impressions_count' => $this->faker->numberBetween(1, 10000),
            'views_count' => $this->faker->numberBetween(1, 10000),
            'zip_file_path' => $this->faker->filePath(),
        ];
    }
}
