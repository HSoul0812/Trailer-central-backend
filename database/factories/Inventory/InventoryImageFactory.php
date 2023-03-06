<?php

use Illuminate\Database\Eloquent\Factory;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryImage;
use Faker\Generator as Faker;

/** @var Factory $factory */

$factory->define(InventoryImage::class, static function (Faker $faker, array $attributes): array {
    return [
        'inventory_id' => $attributes['inventory_id'] ?? factory(Inventory::class)->create()->getKey(),
        'image_id' => $attributes['image_id'],
        'is_default' => $faker->randomElement([0, 1]),
        'is_secondary' => $faker->randomElement([0, 1]),
        'was_manually_added' => $faker->randomElement([0, 1])
    ];
});
