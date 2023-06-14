<?php

/** @var Factory $factory */

use App\Models\User\Location\Geolocation;
use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;

$factory->define(Geolocation::class, static function (Faker $faker, array $attributes): array {
    $attributes['zip'] = $faker->postcode;
    $attributes['city'] = $faker->city;
    $attributes['latitude'] = $faker->latitude;
    $attributes['longitude'] = $faker->longitude;
    $attributes['country'] = ['CA', 'USA'][rand(0, 1)];
    $attributes['state'] = $faker->state;
    return $attributes;
});
