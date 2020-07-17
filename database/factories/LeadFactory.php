<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Tests\Feature\CRM\Leads\AutoAssignCommandTest;
use App\Models\CRM\Leads\Lead;
use Faker\Generator as Faker;

$factory->define(Lead::class, function (Faker $faker) {
    // Select Random Values
    $websiteKey = array_rand(AutoAssignCommandTest::TEST_WEBSITE_ID);
    $locationKey = array_rand(AutoAssignCommandTest::TEST_LOCATION_ID);
    $inventoryKey = array_rand(AutoAssignCommandTest::TEST_INVENTORY_ID);
    $titleKey = array_rand(AutoAssignCommandTest::TEST_FORM_TITLE);

    // Return Overrides
    return [
        'website_id' => AutoAssignCommandTest::TEST_WEBSITE_ID,
        'dealer_id' => AutoAssignCommandTest::TEST_DEALER_ID[$websiteKey],
        'dealer_location_id' => AutoAssignCommandTest::TEST_LOCATION_ID[$locationKey],
        'inventory_id' => AutoAssignCommandTest::TEST_INVENTORY_ID[$inventoryKey],
        'title' => AutoAssignCommandTest::TEST_FORM_TITLE[$titleKey],
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