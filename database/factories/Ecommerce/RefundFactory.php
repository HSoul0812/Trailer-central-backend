<?php

declare(strict_types=1);

/** @var Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use App\Models\Ecommerce\Refund;
use App\Models\Ecommerce\CompletedOrder\CompletedOrder;

$factory->define(Refund::class, static function (Faker $faker, array $attributes): array {
    $createdAt = $faker->dateTimeThisMonth;

    $adjustmentAmount = $faker->numberBetween(100, 1000);

    $refundReasons = collect(Refund::REASONS)->filter(function (string $value, int $key): bool {
        return $value !== Refund::REASON_REQUESTED_BY_TEXTRAIL;
    })->toArray();

    return [
        'order_id' => $attributes['order_id'] ?? factory(CompletedOrder::class)->create()->getKey(),
        'adjustment_amount' => $adjustmentAmount,
        'total_amount' => $adjustmentAmount,
        'reason' => $faker->randomElement($refundReasons),
        'payment_gateway_id' => $faker->uuid,
        'status' => Refund::STATUS_COMPLETED,
        'parts' => [],
        'created_at' => $createdAt
    ];
})->afterMaking(Refund::class, function (Refund $refund, Faker $faker) {
    if (empty($refund->id)) {
        $refund->id = $faker->$faker->numberBetween(100, 10000);
    }

    if (empty($refund->id)) {
        $refund->id = $faker->$faker->numberBetween(100, 10000);
    }
});
