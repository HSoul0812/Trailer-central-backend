<?php

declare(strict_types=1);

/** @var Factory $factory */

use App\Models\Website\PaymentCalculator\Settings;
use App\Models\Website\Website;
use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;

$factory->define(Settings::class, function (Faker $faker, array $attributes) {
    $websiteId = $attributes['website_id'] ?? factory(Website::class)->create()->getKey();

    return [
        'website_id' => $websiteId,
        'entity_type_id' => $faker->numberBetween(1, 10),
        'inventory_condition' => $faker->randomElement([Settings::CONDITION_NEW, Settings::CONDITION_USED]),
        'months' => $faker->numberBetween(1, 12),
        'apr' => $faker->randomFloat(),
        'down' => $faker->randomFloat(),
        'operator' => $faker->randomElement([Settings::OPERATOR_LESS_THAN, Settings::OPERATOR_OVER]),
        'inventory_price' => $faker->randomFloat(),
        'financing' => $faker->randomElement([Settings::FINANCING, Settings::NO_FINANCING]),
    ];
});
