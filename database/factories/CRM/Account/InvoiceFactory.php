<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CRM\Account\Invoice;
use Faker\Generator as Faker;
use Tests\TestCase;

$factory->define(Invoice::class, function (Faker $faker, array $attributes) {
    $dealer_id = $attributes['dealer_id'] ?? TestCase::getTestDealerId();
    $repair_order_id = $attributes['repair_order_id'] ?? null;

    return [
        'dealer_id' => $dealer_id,
        'repair_order_id' => $repair_order_id,
        'customer_id' => 1,
    ];
});
