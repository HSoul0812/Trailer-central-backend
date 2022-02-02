<?php

/** @var Factory $factory */

use App\Models\Parts\Textrail\Type;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Type::class, static function (Faker $faker, array $attributes): array {
    return [
        'name' => $faker->name
    ];
});
