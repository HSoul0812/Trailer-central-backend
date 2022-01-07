<?php

/** @var Factory $factory */

use App\Models\Parts\Textrail\Manufacturer;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Manufacturer::class, static function (Faker $faker, array $attributes): array {
    return [
        'name' => $faker->name
    ];
});
