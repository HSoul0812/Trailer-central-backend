<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CRM\Dms\UnitSale;
use App\Models\CRM\User\Customer;
use Faker\Generator as Faker;
use Tests\TestCase;

$factory->define(UnitSale::class, function (Faker $faker) {
    return [
        'dealer_id' => TestCase::getTestDealerId(),
        'buyer_id' => function () {
            return factory(Customer::class)->create()->id;
        },
        'title' => $faker->title . '_' . rand(10000, 10000000000),
        'sales_location_id' => TestCase::getTestDealerLocationRandom(),
        'inventory_price' => '123.00',
        'total_price' => '456.00',
        'public_note' => '',
        'admin_note' => '',
        'subtotal' => 0.00,
        'is_sold' => false,
        'pdf_path' => '',
    ];
});
