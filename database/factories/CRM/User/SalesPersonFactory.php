<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Tests\TestCase;
use App\Models\CRM\User\SalesPerson;
use App\Models\User\NewDealerUser;
use Faker\Generator as Faker;

$factory->define(SalesPerson::class, function (Faker $faker) {
    // Get Dealer User
    $newDealerUser = NewDealerUser::find(TestCase::TEST_DEALER_ID);

    // Select Random Location
    $locationKey = array_rand(TestCase::TEST_LOCATION_ID);

    // Return Overrides
    return [
        'user_id' => $newDealerUser->crmUser->user_id,
        'dealer_location_id' => TestCase::TEST_LOCATION_ID[$locationKey],
        'perms' => 'user',
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'email' => $faker->unique()->safeEmail,
        'is_default' => 1,
        'is_inventory' => 1,
        'is_financing' => 1,
        'is_rentals' => 1,
        'is_build' => 1,
        'is_trade' => 1,
        'signature' => $faker->paragraph
    ];
});