<?php

declare(strict_types=1);

/** @var Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use App\Models\Ecommerce\Refund;
use App\Models\Ecommerce\CompletedOrder\CompletedOrder;

$factory->define(Refund::class, static function (Faker $faker, array $attributes): array {

    $createdAt = $faker->dateTimeThisMonth;

    return [
        'order_id' => $attributes['order_id'] ?? factory(CompletedOrder::class)->create()->getKey(),
        'amount' => $faker->numberBetween(100, 1000),
        'reason' => $faker->randomElement(Refund::REASONS),
        'object_id' => $faker->uuid,
        'status' => Refund::STATUS_FINISHED,
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
