<?php

/** @var Factory $factory */

use App\Models\Showroom\Showroom;
use App\Models\Showroom\ShowroomFile;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(ShowroomFile::class, static function (Faker $faker, array $attributes): array {
    return [
        'showroom_id' => $attributes['showroom_id'] ?? factory(Showroom::class)->create()->getKey(),
        'src' => $attributes['src'] ?? $faker->url,
        'name' => $attributes['value'] ?? $faker->name,
    ];
});
