<?php

declare(strict_types=1);

/** @var Factory $factory */

use App\Models\User\User;
use App\Models\User\Settings;
use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;

$factory->define(Settings::class, static function (Faker $faker, array $attributes): array {
    // Get Dealer ID
    $dealer_id = $attributes['dealer_id'] ?? factory(User::class)->create()->getKey();

    // Return Array
    return [
        'dealer_id' => $dealer_id,
        'setting' => $settings['setting'] ?? $faker->randomElement(Settings::SETTING_FIELDS),
        'setting_value' => $settings['setting_value'] ?? $faker->words(2, true)
    ];
});
