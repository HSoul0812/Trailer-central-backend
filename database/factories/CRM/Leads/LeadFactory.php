<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadStatus;
use App\Models\Inventory\Inventory;
use App\Models\User\User;
use App\Models\User\DealerLocation;
use App\Models\Website\Website;
use Faker\Generator as Faker;

$factory->define(Lead::class, function (Faker $faker, array $attributes) {
    // Get Dealer ID
    $dealer_id = $attributes['dealer_id'] ?? factory(User::class)->create()->getKey();

    // Get Dealer Location ID
    $dealer_location_id = $attributes['dealer_location_id'] ?? factory(DealerLocation::class)->create([
        'dealer_id' => $dealer_id
    ])->getKey();

    // Get Website ID
    $website_id = $attributes['website_id'] ?? factory(Website::class)->create([
        'dealer_id' => $dealer_id
    ])->getKey();

    // Get Titles
    $leadTypes = ['trade', 'financing', 'build'];
    $formTitles = [
        'trade' => 'Value Your Trade',
        'financing' => 'Financing',
        'build' => 'Build Your Trailer'
    ];

    // Select Random Values
    $typeKey = array_rand($leadTypes);
    $leadType = $leadTypes[$typeKey];

    // Get Random Inventory
    $inventory_id = $attributes['inventory_id'] ?? factory(Inventory::class)->create([
        'dealer_id' => $dealer_id,
        'dealer_location_id' => $dealer_location_id
    ])->getKey();

    // Return Overrides
    return [
        'website_id' => $website_id,
        'dealer_id' => $dealer_id,
        'dealer_location_id' => $dealer_location_id,
        'inventory_id' => $inventory_id,
        'lead_type' => $leadType,
        'title' => $formTitles[$leadType],
        'referral' => $faker->url,
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'email_address' => $faker->unique()->safeEmail,
        'phone_number' => $faker->phoneNumber,
        'address' => $faker->streetAddress,
        'city' => $faker->city,
        'zip' => $faker->postcode,
        'comments' => $faker->realText,
        'note' => $faker->realText,
        'date_submitted' => $faker->dateTimeThisMonth->format('Y-m-d H:i:s')
    ];
});