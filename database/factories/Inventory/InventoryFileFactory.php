<?php

use App\Models\Inventory\InventoryFile;
use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;

/** @var Factory $factory */
$factory->define(InventoryFile::class, static function (Faker $faker, array $attributes): array {
    return [
        'file_id' => $attributes['file_id'],
        'inventory_id' => $attributes['inventory_id'],
        'position' => $attributes['position'] ?? null,
        'is_manual' => $attributes['position'] ?? 0,
    ];
});
