<?php

/** @var Factory $factory */

use App\Models\Parts\Part;
use App\Models\Parts\Vendor;
use App\Models\User\User;
use Faker\Generator as Faker;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Part::class, static function (Faker $faker, array $attributes): array {
    // Get Dealer ID
    $dealer_id = $attributes['dealer_id'] ?? factory(User::class)->create()->getKey();
    $vendor_id = $attributes['vendor_id'] ?? factory(Vendor::class)->create(['dealer_id' => $dealer_id])->getKey();
    // Get Created Date
    $createdAt = $faker->dateTimeThisMonth;

    $price = $attributes['price'] ?? $faker->randomFloat(2, 2000, 9999);

    return [
        'title' => $faker->sentence,
        'vendor_id' => $vendor_id,
        'dealer_id' => $dealer_id,
        'created_at' => $createdAt,
        'updated_at' => $createdAt,
        'sku' => $attributes['sku'] ?? Str::random(17),
        'price' => $price,
        'dealer_cost' => $price / 2,
        'latest_cost' => $price / 2
    ];
});
