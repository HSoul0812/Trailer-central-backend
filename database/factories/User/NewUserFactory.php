<?php

declare(strict_types=1);

/** @var Factory $factory */

use App\Models\User\User;
use App\Models\User\NewUser;
use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;

$factory->define(NewUser::class, static function (Faker $faker, array $attributes): array {

    $data = [
        'email' => $faker->email(),
        'password' => $faker->password()
    ];

    if (isset($attributes['user_id'])) {
        $data['user_id'] = $attributes['user_id'];
    }

    return $data;
});
