<?php

/** @var Factory $factory */

use App\Models\Showroom\Showroom;
use App\Models\Showroom\ShowroomFeature;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(ShowroomFeature::class, static function (Faker $faker, array $attributes): array {
    return [
        'showroom_id' => $attributes['showroom_id'] ?? factory(Showroom::class)->create()->getKey(),
        'feature_list_id' => $attributes['feature_list_id'] ?? $faker->randomNumber(),
        'value' => $attributes['value'] ?? $faker->randomNumber(),
    ];
});
