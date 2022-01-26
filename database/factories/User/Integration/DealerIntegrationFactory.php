<?php

declare(strict_types=1);

/** @var Factory $factory */

use App\Models\User\User;
use App\Models\User\Integration\DealerIntegration;
use App\Models\Integration\Integration;
use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;

$factory->define(DealerIntegration::class, static function (Faker $faker, array $attributes): array {
    if (empty($attributes['integration_id'])) {
        /** @var Integration $integration */
        $integration = Integration::query()->where('active', Integration::STATUS_ACTIVE)->inRandomOrder()->first();

        $attributes['integration_id'] = $integration->integration_id;
    }

    $dealer_id = $attributes['dealer_id'] ?? factory(User::class)->create()->getKey();

    $createdAt = $faker->dateTimeThisMonth;

    return [
        'dealer_id' => $dealer_id,
        'created_at' => $createdAt,
        'updated_at' => $createdAt,
        'integration_id' => $attributes['integration_id'],
        'active' => DealerIntegration::STATUS_ACTIVE
    ];
});
