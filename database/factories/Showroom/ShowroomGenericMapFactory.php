<?php

/** @var Factory $factory */

use App\Models\Showroom\Showroom;
use App\Models\Showroom\ShowroomGenericMap;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(ShowroomGenericMap::class, static function (Faker $faker, array $attributes): array {
    return [
        'showroom_id' => $attributes['showroom_id'] ?? factory(Showroom::class)->create()->getKey(),
        'external_mfg_key' => $attributes['external_mfg_key'] ?? $faker->md5,
    ];
});
