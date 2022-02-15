<?php

declare(strict_types=1);

/** @var Factory $factory */

use App\Models\Parts\Vendor;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;

$factory->define(Vendor::class, static function (Faker $faker, array $attributes): array {
    $dealer_id = $attributes['dealer_id'] ?? factory(User::class)->create()->getKey();

    return [
        'dealer_id' => $dealer_id,
        'name' => $attributes['name'] ?? $faker->name . '_' . $faker->numberBetween(10000, 10000000000),
    ];
});
