<?php

use App\Models\Inventory\Manufacturers\Brand;
use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;

/** @var Factory $factory */

$factory->define(Brand::class, static function (Faker $faker, array $attributes): array {
    return [
        'name' => $attributes['name'] ?? $faker->name,
        'logo' => '',
        'logo_highres' => '',
        'description' => '',
        'manufacturer_id' => $attributes['manufacturer_id'] ?? null,
    ];
});
