<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CRM\Account\Invoice;
use Faker\Generator as Faker;
use Tests\TestCase;

$factory->define(Invoice::class, function (Faker $faker) {
    return [
        'dealer_id' => TestCase::getTestDealerId(),
        'customer_id' => 1,
    ];
});
