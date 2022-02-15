<?php

declare(strict_types=1);

/** @var Factory $factory */

use App\Models\CRM\Dms\ServiceOrder;
use App\Models\CRM\User\Customer;
use App\Models\Inventory\Inventory;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;
use Tests\TestCase;

$factory->define(ServiceOrder::class, static function (Faker $faker, array $attributes): array {

    $dealer_id = $attributes['dealer_id'] ?? factory(User::class)->create()->getKey();
    $customer_id = $attributes['customer_id'] ?? factory(Customer::class)->create(['dealer_id' => $dealer_id])->getKey();
    $inventory_id = $attributes['inventory_id'] ?? factory(Inventory::class)->create(['dealer_id' => $dealer_id])->getKey();

    return [
        'dealer_id' => $dealer_id,
        'customer_id' => $customer_id,
        'inventory_id' => $inventory_id,
        'total_price' => $faker->numberBetween(100, 250),
        'location' => $attributes['location'] ?? TestCase::getTestDealerLocationRandom(),
        'type' => $attributes['type'] ?? $faker->randomElement(ServiceOrder::TYPES),
    ];
});
