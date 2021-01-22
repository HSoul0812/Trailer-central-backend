<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CRM\Leads\LeadImport;
use App\Models\User\User;
use App\Models\User\DealerLocation;
use Faker\Generator as Faker;

$factory->define(LeadImport::class, function (Faker $faker, array $attributes) {
    // Get Dealer ID
    $dealer_id = $attributes['dealer_id'] ?? factory(User::class)->create()->getKey();

    // Get Dealer Location ID
    $dealer_location_id = $attributes['dealer_location_id'] ?? factory(DealerLocation::class)->create([
        'dealer_id' => $dealer_id
    ])->getKey();

    // Return Overrides
    return [
        'dealer_id' => $dealer_id,
        'dealer_location_id' => $dealer_location_id,
        'email' => $faker->unique()->safeEmail
    ];
});