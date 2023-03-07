<?php

declare(strict_types=1);

/** @var Factory $factory */

use App\Models\User\User;
use App\Models\User\NewUser;
use App\Models\User\NewDealerUser;
use App\Models\User\CrmUser;
use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;

$factory->define(NewUser::class, static function (Faker $faker, array $attributes): array {
    $data = [
        'email' => $faker->email(),
        'password' => $faker->password()
    ];

    if (isset($attributes['user_id'])) {
        $data['user_id'] = $attributes['user_id'];
    }

    return $data;
});

$factory->define(NewDealerUser::class, static function (Faker $faker, array $attributes): array {
    $user_id = $attributes['user_id'] ?? factory(User::class)->create()->getKey();

    return [
        'id' => $attributes['id'] ?? $attributes['dealer_id'] ?? $user_id,
        'user_id' => $user_id
    ];
});

$factory->define(CrmUser::class, static function (Faker $faker, array $attributes): array {
    $user_id = $attributes['user_id'] ?? factory(User::class)->create()->getKey();

    return [
        'user_id' => $user_id,
        'logo' => $attributes['logo'] ?? '',
        'first_name' => $attributes['first_name'] ?? '',
        'last_name' => $attributes['last_name'] ?? '',
        'display_name' => $attributes['display_name'] ?? '',
        'state' => $attributes['state'] ?? '',
        'dealer_name' => $attributes['dealer_name'] ?? '',
        'active' => isset($attributes['active']) ? (!empty($attributes['active']) ? 1 : 0) : 1,
        'price_per_mile' => $attributes['price_per_mile'] ?? 0,
        'email_signature' => $attributes['email_signature'] ?? '',
        'timezone' => $attributes['timezone'] ?? env('DB_TIMEZONE'),
        'enable_hot_potato' => isset($attributes['enable_hot_potato']) ? (!empty($attributes['enable_hot_potato']) ? 1 : 0) : 0,
        'disable_daily_digest' => isset($attributes['disable_daily_digest']) ? (!empty($attributes['disable_daily_digest']) ? 1 : 0) : 0,
        'enable_assign_notification' => isset($attributes['enable_assign_notification']) ? (!empty($attributes['enable_assign_notification']) ? 1 : 0) : 1,
        'enable_due_notification' => isset($attributes['enable_due_notification']) ? (!empty($attributes['enable_due_notification']) ? 1 : 0) : 1,
        'enable_past_notification' => isset($attributes['enable_past_notification']) ? (!empty($attributes['enable_past_notification']) ? 1 : 0) : 1,
        'is_factory' => isset($attributes['is_factory']) ? (!empty($attributes['is_factory']) ? 1 : 0) : 0
    ];
});