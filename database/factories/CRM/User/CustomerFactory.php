<?php

declare(strict_types=1);

/** @var Factory $factory */

use App\Models\CRM\User\Customer;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;

$factory->define(Customer::class, static function (Faker $faker, array $attributes): array {

    $firstName = $attributes['first_name'] ?? $faker->firstName();
    $lastName = $attributes['last_name'] ?? $faker->lastName;

    $overrides = [
        'dealer_id' => $attributes['dealer_id'] ?? factory(User::class)->create()->getKey(),
        'first_name' => $firstName,
        'last_name' => $lastName,
        'display_name' => "$firstName $lastName",
        'email' => $attributes['email'] ?? $faker->email
    ];

    if (isset($attributes['id'])) {
        $overrides['id'] = $attributes['id'];
    }

    return $overrides;
});
