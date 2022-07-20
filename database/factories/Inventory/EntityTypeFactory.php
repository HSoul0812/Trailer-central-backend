<?php

use App\Models\Inventory\EntityType;
use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;

/** @var Factory $factory */

$factory->define(EntityType::class, static function (Faker $faker, array $attributes): array {
    return [
        'name' => $faker->name,
        'title' => $faker->name,
        'title_lowercase' => $faker->name,
        'sort_order' => rand(1, 100)
    ];
});
