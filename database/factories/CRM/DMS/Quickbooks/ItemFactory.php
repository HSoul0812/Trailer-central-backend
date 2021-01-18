<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CRM\Dms\Quickbooks\Item;
use Faker\Generator as Faker;
use Tests\TestCase;

$factory->define(Item::class, function (Faker $faker) {
    return [
        'dealer_id' => TestCase::getTestDealerId(),
        'name' => $faker->title . '_' . rand(10000, 10000000000),
        'item_category_id' => 1
    ];
});
