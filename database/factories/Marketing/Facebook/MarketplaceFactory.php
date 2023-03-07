<?php

use App\Models\Marketing\Facebook\Marketplace;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;

/** @var Factory $factory */

$factory->define(Marketplace::class, static function (Faker $faker, array $attributes): array {
    $user_id = $attributes['dealer_id'] ?? factory(User::class)->create()->getKey();

    return [
        'dealer_id' => $user_id,
        'fb_username' => $faker->email,
        'fb_password' => $faker->password,
        'tfa_type' => 'authy',
    ];
});
