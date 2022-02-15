<?php

declare(strict_types=1);

/** @var Factory $factory */

use App\Models\CRM\Dms\UnitSale;
use App\Models\CRM\User\Customer;
use App\Models\Inventory\Inventory;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;
use Tests\TestCase;

$factory->define(UnitSale::class, static function (Faker $faker, array $attributes): array {

    $dealer_id = $attributes['dealer_id'] ?? factory(User::class)->create()->getKey();
    $customer_id = $attributes['customer_id'] ?? factory(Customer::class)->create(['dealer_id' => $dealer_id])->getKey();
    $inventory_id = $attributes['inventory_id'] ?? factory(Inventory::class)->create(['dealer_id' => $dealer_id])->getKey();

    return [
        'dealer_id' => $dealer_id,
        'buyer_id' => $customer_id,
        'inventory_id' => $inventory_id,
        'title' => uniqid(),
        'subtotal' =>  $faker->numberBetween(100, 250),
        'is_sold' =>  $faker->numberBetween(0, 1),
        'pdf_path' => '',
        'inventory_price' => $faker->numberBetween(100, 250),
        'total_price' => $faker->numberBetween(100, 250),
        'sales_location_id' => $attributes['location'] ?? TestCase::getTestDealerLocationRandom(),
        'dealer_location_id' => $attributes['location'] ?? TestCase::getTestDealerLocationRandom(),
    ];
});
