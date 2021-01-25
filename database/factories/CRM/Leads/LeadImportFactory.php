<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CRM\Leads\LeadImport;
use App\Models\User\User;
use Faker\Generator as Faker;

$factory->define(LeadImport::class, function (Faker $faker, array $attributes) {
    // Get Dealer ID
    $dealer_id = $attributes['dealer_id'] ?? factory(User::class)->create()->getKey();

    // Return Overrides
    return [
        'dealer_id' => $dealer_id,
        'email' => $faker->unique()->safeEmail
    ];
});