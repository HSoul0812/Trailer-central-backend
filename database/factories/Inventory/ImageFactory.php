<?php

use Illuminate\Database\Eloquent\Factory;
use App\Models\Inventory\Image;
use Faker\Generator as Faker;

/** @var Factory $factory */

$factory->define(Image::class, static function (Faker $faker, array $attributes): array {
    return [
        'filename' => $faker->uuid . '.jpg',
        'hash' => $faker->uuid
    ];
});
