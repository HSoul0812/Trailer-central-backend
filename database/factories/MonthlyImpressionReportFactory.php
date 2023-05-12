<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MonthlyImpressionReportFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'year' => $this->faker->numberBetween(2023, 2040),
            'month' => $this->faker->numberBetween(1, 12),
            'dealer_id' => $this->faker->numberBetween(1, 1000),
            'inventory_id' => $this->faker->unique()->numberBetween(),
            'inventory_title' => $this->faker->sentence(),
            'inventory_type' => $this->faker->word(),
            'inventory_category' => $this->faker->word(),
            'plp_total_count' => $this->faker->numberBetween(0, 1000),
            'pdp_total_count' => $this->faker->numberBetween(0, 1000),
            'tt_dealer_page_total_count' => $this->faker->numberBetween(0, 1000),
        ];
    }
}
