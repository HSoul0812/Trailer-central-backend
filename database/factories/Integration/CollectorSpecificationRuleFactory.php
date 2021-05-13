<?php

/** @var Factory $factory */

use App\Models\Integration\Collector\CollectorSpecification;
use App\Models\Integration\Collector\CollectorSpecificationRule;
use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;

$factory->define(CollectorSpecificationRule::class, function (Faker $faker, array $attributes) {
    $collector_specification_id = $attributes['collector_specification_id'] ?? factory(CollectorSpecification::class)->create()->getKey();
    $condition = $attributes['condition'] ?? 'equal';
    $field = $attributes['field'] ?? $faker->name;
    $value = $attributes['value'] ?? $faker->name;

    return [
        'collector_specification_id' => $collector_specification_id,
        'condition' => $condition,
        'field' => $field,
        'value' => $value,
    ];
});
