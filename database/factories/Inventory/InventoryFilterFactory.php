<?php

use App\Models\Inventory\InventoryFilter;
use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;

/** @var Factory $factory */

$factory->define(InventoryFilter::class, static function (Faker $faker, array $attributes): array {
    return [
        'attribute' => $attributes['attribute'],
        'label' => $attributes['label'] ?? $faker->firstNameMale,
        'type' => $attributes['type'],
        'is_eav' => $attributes['is_eav'] ?? 1,
        'position' => $attributes['position'] ?? random_int(1, 999),
        'sort' => $attributes['sort'] ?? null,
        'sort_dir' => $attributes['sort_dir'] ?? null,
        'prefix' => $attributes['prefix'] ?? null,
        'suffix' => $attributes['suffix'] ?? null,
        'step' => $attributes['step'] ?? null,
        'dependancy' => $attributes['dependancy'] ?? null,
        'is_visible' => $attributes['is_visible'] ?? 1,
        'db_field' => $attributes['db_field'] ?? null
    ];
});
