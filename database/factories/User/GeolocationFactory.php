<?php

use \App\Models\User\Location\Geolocation;
use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;

/** @var Factory $factory */
$factory->define(Geolocation::class, static function (Faker $faker, array $attributes): array {
    return [
        'zip' => $faker->numerify('#######'),
        'latitude' => $faker->latitude,
        'longitude' => $faker->longitude,
    ];
});
