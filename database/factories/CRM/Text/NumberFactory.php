<?php

use App\Models\CRM\Text\Number;
use Illuminate\Database\Eloquent\Factory;
use App\Models\User\User;
use Faker\Generator as Faker;
use Tests\TestCase;

/** @var Factory $factory */
$factory->define(Number::class, function (Faker $faker, array $attributes) {
    // If we don't specifically limit to 12 characters, it will cause issues with tests
    // Because the database will limit the length, but won't alert the model
    $dealerNumber = $attributes['dealer_number'] ?? substr($faker->e164PhoneNumber, 0, 12);
    $customerNumber = $attributes['customer_number'] ?? substr($faker->e164PhoneNumber, 0, 12);
    $twilioNumber = $attributes['twilio_number'] ?? substr($faker->e164PhoneNumber, 0, 12);
    $customerName = $attributes['customer_name'] ?? $faker->name;
    $expirationTime = $attributes['expiration_time'] ??
        time() + (Number::EXPIRATION_TIME * 60 * 60);
    $dealerId = $attributes['dealer_id'] ?? User::find(TestCase::getTestDealerId())->getKey();

    return [
        'dealer_number' => $dealerNumber,
        'customer_number' => $customerNumber,
        'twilio_number' => $twilioNumber,
        'customer_name' => $customerName,
        'expiration_time' => $expirationTime,
        'dealer_id' => $dealerId
    ];
});
