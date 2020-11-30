<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Tests\TestCase;
use App\Models\CRM\User\SalesPerson;
use App\Models\User\NewDealerUser;
use Faker\Generator as Faker;

$factory->define(SalesPerson::class, function (Faker $faker) {
    // Get Dealer User
    $newDealerUser = NewDealerUser::findOrFail(TestCase::getTestDealerId());

    // Return Overrides
    return [
        'id' => 1,
        'user_id' => $newDealerUser->crmUser->user_id,
        'dealer_location_id' => TestCase::getTestDealerLocationRandom(),
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