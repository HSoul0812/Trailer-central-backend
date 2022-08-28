<?php

declare(strict_types=1);

/** @var Factory $factory */

use App\Models\User\User;
use App\Models\User\NewUser;
use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;

$factory->define(NewUser::class, static function (Faker $faker, array $attributes): array {
    $user_id = $attributes['user_id'] ?? factory(User::class)->create()->getKey();

    return [
        'user_id' => $user_id,
        'email' => $user_id . '@trailercentral.com',
        'password' => $faker->password()
    ];
});
