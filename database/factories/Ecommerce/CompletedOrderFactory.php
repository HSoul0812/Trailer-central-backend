<?php

/** @var Factory $factory */

use App\Models\Ecommerce\CompletedOrder\CompletedOrder;
use Faker\Generator as Faker;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factory;
use App\Models\Parts\Textrail\Part;

$factory->define(CompletedOrder::class, static function (Faker $faker, array $attributes): array {
    // Get Created Date
    $createdAt = $faker->dateTimeThisMonth;

    $createParts = static function (): array {
        $part = factory(Part::class)->create([
            'manufacturer_id' => 66,
            'brand_id' => 25,
            'type_id' => 11,
            'category_id' => 8,
        ]);

        return [['id' => $part[0]['id'], 'qty' => 1]];
    };

    return [
        'customer_email' => $faker->email,
        'total_amount' => $faker->randomFloat(2, 20, 9999),
        'payment_method' => $attributes['payment_method'] ?? 'card',
        'payment_status' => $attributes['payment_status'] ?? $faker->randomElement([CompletedOrder::PAYMENT_STATUS_PAID, CompletedOrder::PAYMENT_STATUS_UNPAID]),
        'event_id' => $attributes['event_id'] ?? Str::random(25),
        'object_id' => $attributes['object_id'] ?? Str::random(25),
        'stripe_customer' => $attributes['stripe_customer'] ?? Str::random(18),
        'shipping_address' => $attributes['shipping_address'] ?? $faker->streetAddress,
        'shipping_name' => $attributes['shipping_name'] ?? $faker->name,
        'shipping_country' => $attributes['shipping_country'] ?? $faker->country,
        'shipping_city' => $attributes['shipping_city'] ?? $faker->city,
        'shipping_zip' => $attributes['shipping_zip'] ?? $faker->postcode,
        'shipping_region' => $attributes['shipping_region'] ?? $faker->stateAbbr,
        'billing_address' => $attributes['billing_address'] ?? $faker->streetAddress,
        'billing_name' => $attributes['billing_name'] ?? $faker->name,
        'billing_country' => $attributes['billing_country'] ?? $faker->country,
        'billing_city' => $attributes['billing_city'] ?? $faker->city,
        'billing_zip' => $attributes['billing_zip'] ?? $faker->postcode,
        'billing_region' => $attributes['billing_region'] ?? $faker->stateAbbr,
        'parts' => $attributes['parts'] ?? $createParts(),
        'status' => $attributes['status'] ?? 'dropshipped',
        'created_at' => $createdAt,
        'updated_at' => $createdAt,
    ];
});
