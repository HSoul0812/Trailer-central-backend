<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CRM\Dms\ServiceOrder\LaborCode;
use Faker\Generator as Faker;
use Tests\TestCase;

$factory->define(LaborCode::class, function (Faker $faker) {
    return [
        'dealer_id' => TestCase::getTestDealerId(),
        'name' => $faker->title . '_' . rand(10000, 10000000000),
    ];
});
