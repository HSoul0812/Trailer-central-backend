<?php

use App\Models\CRM\Text\NumberTwilio;
use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;

/** @var Factory $factory */
$factory->define(NumberTwilio::class, function (Faker $faker, array $attributes) {
    return [
        'phone_number' => $attributes['phone_number'] ?? substr($faker->e164PhoneNumber, 0, 12),
    ];
});
