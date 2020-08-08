<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Tests\TestCase;
use App\Models\CRM\Leads\Lead;
use App\Models\Inventory\Inventory;
use Faker\Generator as Faker;

$factory->define(Lead::class, function (Faker $faker) {
    // Get Titles
    $leadTypes = ['trade', 'financing', 'build'];
    $formTitles = [
        'trade' => 'Value Your Trade',
        'financing' => 'Financing',
        'build' => 'Build Your Trailer'
    ];

    // Select Random Values
    $websiteKey = array_rand(TestCase::TEST_WEBSITE_ID);
    $locationKey = array_rand(TestCase::TEST_LOCATION_ID);
    $typeKey = array_rand($leadTypes);
    $leadType = $leadTypes[$typeKey];

    // Get Random Inventory
    $inventory = Inventory::where('dealer_id', TestCase::TEST_DEALER_ID)->inRandomOrder()->first();

    // Return Overrides
    return [
        'website_id' => TestCase::TEST_WEBSITE_ID[$websiteKey],
        'dealer_id' => TestCase::TEST_DEALER_ID,
        'dealer_location_id' => TestCase::TEST_LOCATION_ID[$locationKey],
        'inventory_id' => $inventory->inventory_id,
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
        'date_submitted' => $faker->dateTimeThisMonth
    ];
});