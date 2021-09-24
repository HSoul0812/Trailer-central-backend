<?php

declare(strict_types=1);

/** @var Factory $factory */

use App\Models\Website\Website;
use App\Models\Website\Config\WebsiteConfig;
use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;

$factory->define(WebsiteConfig::class, static function (Faker $faker, array $attributes): array {
    // Get Website
    $website_id = $attributes['webiste_id'] ?? factory(Website::class)->create()->getKey();

    // Return Array
    return [
        'website_id' => $website_id,
        'key' => $attributes['key'] ?? 'parts/ecommerce/enabled',
        'value' => $attributes['key'] ?? 1
    ];
});