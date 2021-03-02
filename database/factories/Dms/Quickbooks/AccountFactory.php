<?php

declare(strict_types=1);

/** @var Factory $factory */

use App\Models\CRM\Dms\Quickbooks\Account;
use App\Models\User\User;
use Faker\Generator as Faker;

$factory->define(Account::class, static function (Faker $faker, array $attributes): array {
    $dealer_id = $attributes['dealer_id'] ?? factory(User::class)->create()->getKey();

    return [
        'dealer_id' => $dealer_id,
        'name' => $attributes['name'] ?? $faker->name . '_' . rand(10000, 10000000000),
        'type' => $attributes['type'] ?? $faker->randomElement(Account::ACCOUNT_TYPES),
        'sub_type' => $attributes['sub_type'] ?? $faker->name . '_' . rand(10000, 10000000000),
    ];
});
