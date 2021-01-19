<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CRM\Dms\ServiceOrder;
use App\Models\CRM\Dms\UnitSale;
use App\Models\Inventory\Inventory;
use Faker\Generator as Faker;
use Tests\TestCase;

$factory->define(ServiceOrder::class, function (Faker $faker) {
    return [
        'dealer_id' => TestCase::getTestDealerId(),
        'customer_id' => $faker->randomDigit,
        'inventory_id' => function () {
            return factory(Inventory::class)->create()->inventory_id;
        },
        'unit_sale_id' => function () {
            return factory(UnitSale::class)->create()->id;
        },
        'location' => $faker->randomDigit,
        'status' => 'picked_up',
        'total_price' => $faker->randomFloat(),
    ];
});
