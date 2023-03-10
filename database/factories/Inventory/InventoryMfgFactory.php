<?php

use App\Models\Inventory\InventoryMfg;
use App\Models\Inventory\Manufacturers\Manufacturers;
use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;

/** @var Factory $factory */

$factory->define(InventoryMfg::class, static function (Faker $faker, array $attributes): array {
    return [
        'name' => $attributes['name'] ?? $faker->name,
        'label' => $faker->name,
        'website' => '',
        'address' => '',
        'phone' => '',
        'note' => '',
    ];
});

$factory->define(Manufacturers::class, static function (Faker $faker, array $attributes): array {
    return [
        'name' => $attributes['name'] ?? $faker->name,
        'logo' => '',
        'logo_highres' => '',
        'description' => ''
    ];
});
