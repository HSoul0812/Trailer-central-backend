<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CRM\Dms\ServiceOrder;
use App\Models\CRM\Dms\ServiceOrder\ServiceItem;
use Faker\Generator as Faker;

$factory->define(ServiceItem::class, function (Faker $faker) {
    return [
        'repair_order_id' => function () {
            return factory(ServiceOrder::class)->create()->id;
        },
        'repair_no' => $faker->title . '_' . rand(10000, 10000000000),
    ];
});
