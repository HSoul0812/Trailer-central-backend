<?php

use App\Models\Inventory\Inventory;
use App\Models\Marketing\Craigslist\Profile;
use App\Models\Marketing\Craigslist\Queue;
use App\Models\User\User;
use Carbon\Carbon;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */

$factory->define(Queue::class, static function (Faker $faker, array $attributes): array {
    // Get Dealer
    $user = isset($attributes['dealer_id']) ? null : factory(User::class)->create();
    $dealer_id = $user ? $user->getKey() : $attributes['dealer_id'];

    // Get Inventory
    $inventory = isset($attributes['inventory_id']) ? null : factory(Inventory::class)->create([
        'dealer_id' => $dealer_id
    ]);
    $inventory_id = $inventory ? $inventory->getKey() : $attributes['inventory_id'];

    // Get Profile
    $profile = isset($attributes['profile_id']) ? null : factory(Profile::class)->create([
        'dealer_id' => $dealer_id
    ]);
    $profile_id = $profile ? $profile->getKey() : $attributes['profile_id'];

    // Configure Return Array
    return [
        'session_id' => $attributes['session_id'] ?? $faker->regexify('[A-Za-z0-9]{20}'),
        'parent_id' => $attributes['parent_id'] ?? null,
        'time' => $attributes['time'] ?? Carbon::now(),
        'command' => $attributes['command'] ?? 'postDelete',
        'parameter' => $attributes['parameter'] ?? '',
        'dealer_id' => $dealer_id,
        'profile_id' => $profile_id,
        'inventory_id' => $inventory_id,
        'status' => $attributes['status'] ?? 'unprocessed',
        'state' => $attributes['state'] ?? 'new',
        'img_state' => $attributes['img_state'] ?? '',
        'costs' => $attributes['costs'] ?? 0,
        'log' => $attributes['log'] ?? '',
    ];
});
