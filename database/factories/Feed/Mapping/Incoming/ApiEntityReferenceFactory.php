<?php

/** @var Factory $factory */

use App\Models\Feed\Mapping\Incoming\ApiEntityReference;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(ApiEntityReference::class, static function (Faker $faker, array $attributes): array {
    if (empty($attributes['entity_type'])) {
        throw new InvalidArgumentException("'entity_type' is required");
    }

    if (empty($attributes['entity_id'])) {
        throw new InvalidArgumentException("'entity_id' is required");
    }

    return [
        'entity_type' => $attributes['entity_type'],
        'entity_id' => $attributes['entity_id'],
        'reference_id' => $attributes['reference_id'] ?? $faker->uuid,
        'api_key' => $attributes['api_key'] ?? $faker->randomElement(['lt', 'pj', 'utc', 'lgs', 'novae', 'test', 'lamar', 'norstar']),
    ];
});
