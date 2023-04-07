<?php

/** @var Factory $factory */

use App\Models\Showroom\Showroom;
use App\Models\Showroom\ShowroomImage;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(ShowroomImage::class, static function (Faker $faker, array $attributes): array {
    return [
        'showroom_id' => $attributes['showroom_id'] ?? factory(Showroom::class)->create()->getKey(),
        'src' => $attributes['src'] ?? $faker->url,
        'is_floorplan' => $attributes['is_floorplan'] ?? $faker->boolean,
        'has_stock_overlay' => $attributes['has_stock_overlay'] ?? $faker->boolean,
    ];
});
