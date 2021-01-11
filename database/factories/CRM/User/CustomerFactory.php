<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CRM\User\Customer;
use Faker\Generator as Faker;
use Tests\TestCase;

$factory->define(Customer::class, function (Faker $faker) {
    return [
        'dealer_id' => TestCase::getTestDealerId(),
        'display_name' => $faker->name,
    ];
});
