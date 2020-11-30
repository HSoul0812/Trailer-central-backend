<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Integration\Auth\AccessToken;
use Faker\Generator as Faker;

/**
 * Define Access Token Factory
 */
$factory->define(AccessToken::class, function (Faker $faker) {
    // Return Overrides
    $time = time();
    return [
        'dealer_id' => 1001,
        'token_type' => 'google',
        'relation_type' => 'sales_person',
        'relation_id' => $_ENV['TEST_GOOGLE_RELATION_ID'],
        'access_token' => $_ENV['TEST_GOOGLE_ACCESS_TOKEN'],
        'refresh_token' => $_ENV['TEST_GOOGLE_REFRESH_TOKEN'],
        'id_token' => $_ENV['TEST_GOOGLE_ID_TOKEN'],
        'expires_in' => $_ENV['TEST_GOOGLE_EXPIRES_IN'],
        'expires_at' => date("Y-m-d H:i:s", $time + $_ENV['TEST_GOOGLE_EXPIRES_IN']),
        'issued_at' => date("Y-m-d H:i:s", $time)
    ];
});