<?php

declare(strict_types=1);

/** @var Factory $factory */

use App\Models\User\User;
use App\Models\User\DealerLocation;
use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;

$factory->define(DealerLocation::class, static function (Faker $faker, array $attributes): array {
    // Get Dealer ID
    $dealer_id = $attributes['dealer_id'] ?? factory(User::class)->create()->getKey();

    // Created At
    $createdAt = $faker->dateTimeThisMonth;

    // Return Array
    return [
        'dealer_id' => $dealer_id,
        'created_at' => $createdAt,
        'updated_at' => $createdAt,
        'is_default' => $attributes['is_default'] ?? 0,
        'is_default_for_invoice' => $attributes['is_default_for_invoice'] ?? 0,
        'name' => $attributes['name'] ?? $faker->company,
        'contact' => $attributes['contact'] ?? $faker->name,
        'website' => $attributes['website'] ?? $faker->domainName,
        'phone' => $attributes['phone'] ?? $faker->phoneNumber,
        'fax' => $attributes['fax'] ?? $faker->phoneNumber,
        'email' => $attributes['email'] ?? $faker->freeEmail,
        'address' => $attributes['address'] ?? $faker->streetAddress,
        'city' => $attributes['city'] ?? $faker->city,
        'county' => $attributes['county'] ?? $faker->city,
        'region' => $attributes['region'] ?? $faker->stateAbbr,
        'postalcode' => $attributes['postalcode'] ?? substr($faker->postcode, 0, 5),
        'country' => $attributes['country'] ?? $faker->country,
        'latitude' => $attributes['latitude'] ?? $faker->latitude,
        'longitude' => $attributes['longitude'] ?? $faker->longitude,
        'sms' => $attributes['sms'] ?? 1,
        'sms_phone' => $attributes['sms_phone'] ?? $faker->phoneNumber,
    ];
});
