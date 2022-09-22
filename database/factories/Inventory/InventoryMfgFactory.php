<?php

use App\Models\Inventory\InventoryMfg;
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
