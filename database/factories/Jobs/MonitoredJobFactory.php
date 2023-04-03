<?php

/** @var Factory $factory */

use App\Models\Common\MonitoredJob;
use Illuminate\Database\Eloquent\Factory;
use App\Models\User\User;
use Faker\Generator as Faker;
use Ramsey\Uuid\Uuid;

$factory->define(MonitoredJob::class, static function (Faker $faker, array $attributes): array {
    // Get Dealer ID
    $dealerId = $attributes['dealer_id'] ?? factory(User::class)->create()->getKey();

    $date = $faker->dateTimeThisMonth;

    return [
        'token' => $attributes['token'] ?? Uuid::uuid4()->toString(),
        'dealer_id' => $dealerId,
        'progress' => $faker->randomFloat(2, 1, 99),
        'queue' => $faker->word(),
        'name' => $faker->slug(3),
        'concurrency_level' => $attributes['concurrency_level'] ?? MonitoredJob::LEVEL_DEFAULT,
        'created_at' => $date,
        'updated_at' => $date
    ];
});
