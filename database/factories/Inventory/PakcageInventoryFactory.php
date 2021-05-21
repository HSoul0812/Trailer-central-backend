<?php

use App\Models\Inventory\Inventory;
use App\Models\Inventory\Packages\Package;
use App\Models\Inventory\Packages\PackageInventory;
use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;

/** @var Factory $factory */

$factory->define(PackageInventory::class, static function (Faker $faker, array $attributes): array {
    $inventory_id = $attributes['inventory_id'] ??  factory(Inventory::class)->create()->getKey();
    $package_id = $attributes['package_id'] ??  factory(Package::class)->create()->getKey();
    $is_main_item = $attributes['is_main_item'] ?? false;

    return [
        'inventory_id' => $inventory_id,
        'package_id' => $package_id,
        'is_main_item' => $is_main_item
    ];
});
