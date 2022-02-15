<?php

/** @var Factory $factory */

use App\Models\Integration\Collector\CollectorSpecification;
use App\Models\Integration\Collector\CollectorSpecificationAction;
use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;

$factory->define(CollectorSpecificationAction::class, function (Faker $faker, array $attributes) {
    $collector_specification_id = $attributes['collector_specification_id'] ?? factory(CollectorSpecification::class)->create()->getKey();
    $action = $attributes['action'] ?? 'skip_item';
    $field = $attributes['field'] ?? null;
    $value = $attributes['value'] ?? null;

    return [
        'collector_specification_id' => $collector_specification_id,
        'action' => $action,
        'field' => $field,
        'value' => $value,
    ];
});
