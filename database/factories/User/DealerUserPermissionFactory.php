<?php

use App\Models\User\DealerUser;
use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;

/** @var Factory $factory */

$factory->define(\App\Models\User\DealerUserPermission::class, static function (Faker $faker, array $attributes): array {
    $user = isset($attributes['dealer_user_id']) ? null : factory(DealerUser::class)->create();
    $userId = $user ? $user->getKey() : $attributes['dealer_user_id'];

    return [
        'dealer_user_id' => $userId,
        'feature' => $attributes['feature'],
        'permission_level' => $attributes['permission_level'],
    ];
});
