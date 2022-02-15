<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CRM\Dms\ServiceOrder\ServiceItem;
use App\Models\CRM\Dms\ServiceOrder\ServiceItemTechnician;
use App\Models\CRM\Dms\ServiceOrder\Technician;
use Faker\Generator as Faker;

$factory->define(ServiceItemTechnician::class, function (Faker $faker) {
    return [
        'service_item_id' => function () {
            factory(ServiceItem::class)->create([])->id;
        },
        'dms_settings_technician_id' => function () {
            factory(Technician::class)->create([])->id;
        }
    ];
});
