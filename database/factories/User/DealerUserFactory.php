<?php

use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;

/** @var Factory $factory */

$factory->define(\App\Models\User\DealerUser::class, static function (Faker $faker, array $attributes): array {
    return [
        'dealer_id' => $attributes['dealer_id'],
        'name' => $faker->name,
    ];
});
