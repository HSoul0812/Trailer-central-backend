<?php

use App\Models\Inventory\Packages\Package;
use Illuminate\Database\Eloquent\Factory;
use App\Models\User\User;
use Faker\Generator as Faker;

/** @var Factory $factory */

$factory->define(Package::class, static function (Faker $faker, array $attributes): array {
    $dealer_id = $attributes['dealer_id'] ??  factory(User::class)->create()->getKey();
    $visible_with_main_item = $attributes['visible_with_main_item'] ?? false;

    return [
        'dealer_id' => $dealer_id,
        'visible_with_main_item' => $visible_with_main_item
    ];
});
