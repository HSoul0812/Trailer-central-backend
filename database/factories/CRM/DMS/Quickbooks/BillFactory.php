<?php

/** @var Factory $factory */

use App\Models\CRM\Dms\Quickbooks\Bill;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Bill::class, function (Faker $faker) {
    return [
        'dealer_id' => $faker->numberBetween(1, 10000),
        'dealer_location_id' => null,
        'vendor_id' => $faker->numberBetween(1, 10000),
        'doc_num' => null,
        'total' => $faker->numberBetween(1, 10000),
        'received_date' => null,
        'due_date' => null,
        'memo' => null,
        'packing_list_no' => null,
        'status' => $faker->randomElement([Bill::STATUS_DUE, Bill::STATUS_PAID]),
        'qb_id' => null,
    ];
});
