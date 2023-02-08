<?php

use App\Models\Inventory\File;
use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;

/** @var Factory $factory */
$factory->define(File::class, static function (Faker $faker, array $attributes): array {
    return [
        'title' => $attributes['title'] ?? $faker->title,
        'path' => $attributes['path'] ?? $faker->url,
        'type' => $attributes['type'] ?? 'text/plain',
        'size' => $attributes['size'] ?? 0,
        'created_at' => new \DateTime(),
        'updated_at' => new \DateTime(),
        'is_active' => $attributes['is_active'] ?? 1
    ];
});
