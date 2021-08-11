<?php

use App\Models\CRM\Dms\Refund;
use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;
use App\Models\User\User;

/** @var Factory $factory */

$factory->define(Refund::class, function (Faker $faker, array $attributes) {
    $dealer_id = $attributes['dealer_id'] ?? factory(User::class)->create()->getKey();
    $tb_name = $attributes['tb_name'] ?? 'qb_payment';
    $tb_primary_id = $attributes['tb_primary_id'] ?? $faker->randomDigit;
    $amount = $attributes['amount'] ?? $faker->randomDigit;
    $register_id = $attributes['register_id'] ?? $faker->randomDigit;

    return [
        'dealer_id' => $dealer_id,
        'tb_name' => $tb_name,
        'tb_primary_id' => $tb_primary_id,
        'amount' => $amount,
        'register_id' => $register_id
    ];
});
