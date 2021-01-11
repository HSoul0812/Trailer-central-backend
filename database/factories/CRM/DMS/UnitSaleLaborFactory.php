<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CRM\Dms\ServiceOrder\LaborCode;
use App\Models\CRM\Dms\UnitSale;
use App\Models\CRM\Dms\UnitSaleLabor;
use Faker\Generator as Faker;

$factory->define(UnitSaleLabor::class, function (Faker $faker) {
    return [
        'unit_sale_id' => function () {
            return factory(UnitSale::class)->create()->id;
        },
        'quantity' => 1,
        'unit_price' => 123.00,
        'dealer_cost' => 456.00,
        'labor_code' => function () {
            return factory(LaborCode::class)->create()->id;
        },
        'status' => 'open',
        'cause' => $faker->title,
        'actual_hours' => 11.00,
        'paid_hours' => 22.00,
        'billed_hours' => 33.00,
        'technician' => $faker->name,
        'notes' => $faker->title
    ];
});
