<?php

declare(strict_types=1);

/** @var Factory $factory */

use App\Models\Website\Image\WebsiteImage;

use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;

$factory->define(WebsiteImage::class, function (Faker $faker): array {
    return [
        'title' => $faker->sentence(),
        'image' => $faker->url,
        'description' => $faker->sentence(),
        'link' => $faker->url,
        'sort_order' => rand(0, 100),
        'date_created' => now(),
        'is_active' => rand(0, 1)
    ];
});
