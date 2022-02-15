<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CRM\Dms\ServiceOrder\Technician;
use Faker\Generator as Faker;
use Tests\TestCase;

$factory->define(Technician::class, function (Faker $faker) {
    return [
        'dealer_id' => TestCase::getTestDealerId(),
        'first_name' => $faker->name . '_' . rand(10000, 10000000000),
        'last_name' => $faker->name . '_' . rand(10000, 10000000000),
        'email' => $faker->email,
    ];
});
