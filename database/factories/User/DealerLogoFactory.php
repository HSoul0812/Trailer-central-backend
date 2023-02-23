<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\User\DealerLogo;
use Faker\Generator as Faker;

$factory->define(DealerLogo::class, function (Faker $faker) {
    return [
        'filename' => $faker->imageUrl(),
        'benefit_statement' => $faker->sentence()
    ];
});
