<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Tests\TestCase;
use App\Models\CRM\User\SalesPerson;
use App\Models\User\NewDealerUser;
use Faker\Generator as Faker;

$factory->define(SalesPerson::class, function (Faker $faker) {
    // Get Dealer User
    $newDealerUser = NewDealerUser::findOrFail(TestCase::getTestDealerId());

    // Initialize Email
    $email = $faker->unique()->safeEmail;
    $password = $faker->password;
    $server = $faker->domain;

    // Return Overrides
    return [
        'id' => 1,
        'user_id' => $newDealerUser->crmUser->user_id,
        'dealer_location_id' => TestCase::getTestDealerLocationRandom(),
        'perms' => 'user',
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'email' => $email,
        'is_default' => 1,
        'is_inventory' => 1,
        'is_financing' => 1,
        'is_rentals' => 1,
        'is_build' => 1,
        'is_trade' => 1,
        'signature' => $faker->paragraph,
        'smtp_email' => $email,
        'smtp_password' => $password,
        'smtp_server' => 'smtp.' . $server,
        'smtp_port' => '993',
        'smtp_security' => 'ssl',
        'smtp_auth' => array_rand(SalesPerson::AUTH_TYPES),
        'smtp_failed' => 0,
        'smtp_error' => 0,
        'imap_email' => $email,
        'imap_password' => $password,
        'imap_server' => 'imap.' . $server,
        'imap_port' => '993',
        'imap_security' => 'ssl',
        'imap_failed' => 0
    ];
});