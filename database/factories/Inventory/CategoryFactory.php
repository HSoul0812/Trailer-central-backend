<?php

use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;

/** @var Factory $factory */

$factory->define(\App\Models\Inventory\Category::class, static function (Faker $faker, array $attributes): array {
    return [
        'entity_type_id' => $attributes['entity_type_id'] ?? 1,
        'category' => $attributes['category'] ?? $faker->name,
        'label' => $attributes['label'] ?? $faker->name,
        'legacy_category' => $attributes['legacy_category'] ?? $faker->name,
    ];
});
