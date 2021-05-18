<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CRM\Dms\Quickbooks\Item;
use Faker\Generator as Faker;
use Tests\TestCase;

$factory->define(Item::class, function (Faker $faker, array $attributes) {
    $dealer_id = $attributes['dealer_id'] ?? TestCase::getTestDealerId();

    return [
        'dealer_id' => $dealer_id,
        'name' => $faker->title . '_' . rand(10000, 10000000000),
        'item_category_id' => 1
    ];
});
