<?php

declare(strict_types=1);

/** @var Factory $factory */

use App\Models\CRM\Dms\ServiceOrder;
use App\Models\CRM\Dms\ServiceOrder\ServiceItem;
use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;

$factory->define(ServiceItem::class, static function (Faker $faker, array $attributes): array {

    $repair_order_id = $attributes['repair_order_id'] ?? factory(ServiceOrder::class)->create()->getKey();
    $quantity = $faker->numberBetween(1, 5);

    return [
        'repair_order_id' => $repair_order_id,
        'repair_no' => $faker->numberBetween(100, 1000),
        'quantity' => $quantity,
        'amount' => $faker->numberBetween(10, 20) * $quantity
    ];
});
