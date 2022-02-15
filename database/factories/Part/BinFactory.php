<?php

/** @var Factory $factory */

use App\Models\Parts\Bin;
use App\Models\User\DealerLocation;
use App\Models\User\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Bin::class, static function (Faker $faker, array $attributes): array {

    $dealer_id = $attributes['dealer_id'] ?? factory(User::class)->create()->getKey();
    $dealer_location_id = $attributes['location'] ?? factory(DealerLocation::class)->create(['dealer_id' => $dealer_id])->dealer_location_id;

    return [
        'dealer_id' => $dealer_id,
        'location' => $dealer_location_id,
        'bin_name' => 'Bin ' . $faker->realText(30),
    ];
});
