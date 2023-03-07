<?php

/** @var Factory $factory */

use App\Models\User\AuthToken;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;

$factory->define(AuthToken::class, static function (Faker $faker, array $attributes): array {
    $user = $attributes['user_id'] ? User::find($attributes['user_id']) : factory(User::class)->create();
    $userId = $user ? $user->getKey() : $attributes['user_id'];
    $userType = $attributes['user_type'] ?? 'dealer';

    return [
        'user_id' => $userId,
        'user_type' => $userType,
        'access_token' => $faker->password(),
        'created_at' => new \DateTime(),
    ];
});
