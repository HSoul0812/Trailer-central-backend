<?php

use App\Models\Inventory\Inventory;
use App\Models\Marketing\Craigslist\Queue;
use App\Models\Marketing\Craigslist\Transaction;
use App\Models\User\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */

$factory->define(Transaction::class, static function (Faker $faker, array $attributes): array {
    // Get Dealer
    $user = isset($attributes['dealer_id']) ? null : factory(User::class)->create();
    $dealer_id = $user ? $user->getKey() : $attributes['dealer_id'];

    // Get Queue
    $queue = isset($attributes['queue_id']) ? null : factory(Queue::class)->create([
        'dealer_id' => $dealer_id
    ]);
    $queue_id = $queue ? $queue->getKey() : $attributes['queue_id'];

    // Get Inventory
    $inventory = isset($attributes['inventory_id']) ? null : factory(Inventory::class)->create([
        'dealer_id' => $dealer_id
    ]);
    $inventory_id = $inventory ? $inventory->getKey() : $attributes['inventory_id'];

    // Configure Return Array
    return [
        'dealer_id' => $dealer_id,
        'ip_addr' => $faker->ipv4,
        'user_agent' => $faker->userAgent,
        'session_id' => $attributes['session_id'] ?? $faker->regexify('[A-Za-z0-9]{20}'),
        'queue_id' => $queue_id,
        'inventory_id' => $inventory_id,
        'amount' => $faker->randomFloat(2),
        'balance' => $faker->randomFloat(2),
        'type' => $attributes['type'] ?? $faker->randomElements(Transaction::TYPES)
    ];
});
