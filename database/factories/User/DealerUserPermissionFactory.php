<?php

use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;

/** @var Factory $factory */

$factory->define(\App\Models\User\DealerUserPermission::class, static function (Faker $faker, array $attributes): array {
    return [
        'dealer_user_id' => $attributes['dealer_user_id'],
        'feature' => $attributes['feature'],
        'permission_level' => $attributes['permission_level'],
    ];
});
