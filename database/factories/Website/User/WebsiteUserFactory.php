<?php
declare(strict_types=1);

/** @var Factory $factory */

use App\Models\Website\User\WebsiteUser;

use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;

$factory->define(WebsiteUser::class, function (Faker $faker): array {
    return [
        'first_name' => $faker->firstName,
        'middle_name' => '',
        'last_name' => $faker->lastName,
        'email' => $faker->email,
        'password' => $faker->password,
    ];
});

