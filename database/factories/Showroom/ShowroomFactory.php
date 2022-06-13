<?php

/** @var Factory $factory */

use App\Models\Showroom\Showroom;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Showroom::class, static function (Faker $faker, array $attributes): array {
    return $attributes;
});
