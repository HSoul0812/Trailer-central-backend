<?php

use App\Models\User\User;
use App\Models\User\DealerClapp;
use Carbon\Carbon;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */

$factory->define(DealerClapp::class, static function (Faker $faker, array $attributes): array {
    $user = isset($attributes['dealer_id']) ? null : factory(User::class)->create();
    $userId = $user ? $user->getKey() : $attributes['dealer_id'];

    return [
        'dealer_id' => $userId,
        'slots' => 1,
        'chrome_mode' => 0,
        'since' => Carbon::now()->toDateTimeString()
    ];
});
