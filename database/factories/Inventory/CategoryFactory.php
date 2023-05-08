<?php

use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;

/** @var Factory $factory */

$factory->define(\App\Models\Inventory\Category::class, static function (Faker $faker, array $attributes): array {
    $params = [
        'entity_type_id' => $attributes['entity_type_id'] ?? 1,
        'category' => $attributes['category'] ?? $faker->name,
        'label' => $attributes['label'] ?? $faker->name,
        'legacy_category' => $attributes['legacy_category'] ?? $faker->name,
    ];

    if (isset($attributes['inventory_category_id'])) {
        $params['inventory_category_id'] = $attributes['inventory_category_id'];
    }

    return $params;
});
