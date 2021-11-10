<?php

use App\Models\Inventory\CustomOverlay;
use Illuminate\Database\Eloquent\Factory;
use App\Models\User\User;
use Faker\Generator as Faker;

/** @var Factory $factory */

$factory->define(CustomOverlay::class, static function (Faker $faker, array $attributes): array {
    return [
        'dealer_id' => $attributes['dealer_id'] ?? factory(User::class)->create()->getKey(),
        'name' => $faker->randomElement(CustomOverlay::VALID_CUSTOM_NAMES),
        'value' => $faker->sentence()
    ];
});
