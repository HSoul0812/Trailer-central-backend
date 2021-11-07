<?php

/** @var Factory $factory */

use App\Models\User\DealerLocationMileageFee;
use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;

$factory->define(DealerLocationMileageFee::class, function (Faker $faker, array $attributes): array {
    return [
        'inventory_category_id' => 1,
        'fee_per_mile' => $faker->randomFloat(2, 0, 99)
    ];
});
