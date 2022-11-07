<?php

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

/** @var Factory $factory */

$factory->define(\App\Models\User\DealerUser::class, static function (Faker $faker, array $attributes): array {
    $user = isset($attributes['dealer_id']) ? null : factory(User::class)->create();
    $userId = $user ? $user->getKey() : $attributes['dealer_id'];

    return [
        'dealer_id' => $userId,
        'name' => $faker->name,
        'email' => $faker->safeEmail(),
        'salt' => uniqid(),
        'password' => Str::random(),
    ];
});
