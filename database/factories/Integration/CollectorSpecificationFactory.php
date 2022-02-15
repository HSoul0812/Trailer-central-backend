<?php

/** @var Factory $factory */

use App\Models\Integration\Collector\Collector;
use App\Models\Integration\Collector\CollectorSpecification;
use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;

$factory->define(CollectorSpecification::class, function (Faker $faker, array $attributes) {
    $collector_id = $attributes['collector_id'] ?? factory(Collector::class)->create()->getKey();
    $logical_operator = $attributes['logical_operator'] ?? 'and';

    return [
        'collector_id' => $collector_id,
        'logical_operator' => $logical_operator,
    ];
});
