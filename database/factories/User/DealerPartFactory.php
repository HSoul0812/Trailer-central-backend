<?php

declare(strict_types=1);

/** @var Factory $factory */

use App\Models\User\User;
use App\Models\User\DealerPart;
use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;

$factory->define(DealerPart::class, static function (Faker $faker, array $attributes): array {
    // Get Website
    $dealer_id = $attributes['dealer_id'] ?? factory(Website::class)->create()->getKey();

    // Return Array
    return [
        'dealer_id' => $dealer_id,
        'since' => new \DateTime()
    ];
});