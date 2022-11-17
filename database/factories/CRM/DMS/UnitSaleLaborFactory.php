<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CRM\Dms\ServiceOrder\LaborCode;
use App\Models\CRM\Dms\UnitSale;
use App\Models\CRM\Dms\UnitSaleLabor;
use Faker\Generator as Faker;

$factory->define(UnitSaleLabor::class, function (Faker $faker, array $attributes = []) {
    return [
        'unit_sale_id' => $attributes['unit_sale_id'] ?? function () {
            return factory(UnitSale::class)->create()->getKey();
        },
        'quantity' => 1,
        'unit_price' => 123.00,
        'dealer_cost' => 456.00,
        'labor_code' => function () {
            return factory(LaborCode::class)->create()->id;
        },
        'status' => 'open',
        'cause' => $faker->title . '_' . rand(10000, 10000000000),
        'actual_hours' => 11.00,
        'paid_hours' => 22.00,
        'billed_hours' => 33.00,
        'technician' => $faker->name . '_' . rand(10000, 10000000000),
        'notes' => $faker->title . '_' . rand(10000, 10000000000),
    ];
});
