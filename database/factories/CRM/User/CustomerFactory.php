<?php

declare(strict_types=1);

/** @var Factory $factory */

use App\Models\CRM\User\Customer;
use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;
use Tests\TestCase;

$factory->define(Customer::class, static function (Faker $faker, array $attributes): array {

    $firstName = $attributes['first_name'] ?? $faker->firstName();
    $lastName = $attributes['last_name'] ?? $faker->lastName;

    return [
        'dealer_id' => $attributes['dealer_id'] ?? TestCase::getTestDealerId(),
        'first_name' => $firstName,
        'last_name' => $lastName,
        'display_name' => "$firstName $lastName",
        'email' => $attributes['email'] ?? $faker->email
    ];
});
