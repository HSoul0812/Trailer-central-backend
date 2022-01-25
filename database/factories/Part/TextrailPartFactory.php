<?php

/** @var Factory $factory */

use App\Models\Parts\Textrail\Part;
use Faker\Generator as Faker;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Part::class, static function (Faker $faker, array $attributes): array {
    // Get Created Date
    $createdAt = $faker->dateTimeThisMonth;

    $price = $attributes['price'] ?? $faker->randomFloat(2, 2000, 9999);

    return [
        'title' => $faker->sentence,
        'created_at' => $createdAt,
        'updated_at' => $createdAt,
        'sku' => $attributes['sku'] ?? Str::random(17),
        'price' => $price,
        'dealer_cost' => $price / 2,
        'latest_cost' => $price / 2,
        'subcategory' => $faker->name,
        'qty' => $faker->numberBetween(0, 100),
        'msrp' => $faker->randomFloat(2, 20, 9999),
        'weight' => $faker->numberBetween(10, 200),
        'weight_rating' => $faker->numberBetween(10, 200),
        'description' => $faker->sentence,
        'show_on_website' => $faker->numberBetween(0,1),
        'is_vehicle_specific' => $faker->numberBetween(0,1)
    ];
});
