<?php

declare(strict_types=1);

/** @var Factory $factory */

use App\Models\Inventory\Inventory;
use App\Models\Parts\Part;
use App\Models\User\User;
use App\Models\User\DealerLocation;
use App\Models\Website\Tracking\Tracking;
use App\Models\Website\Tracking\TrackingUnit;
use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;

$factory->define(Tracking::class, static function (Faker $faker, array $attributes): array {
    // Get Session ID
    $sessionId = '';
    for($i = 0; $i < 28; $i++) {
        $sessionId .= $faker->randomDigit();
    }

    // Return Array
    return [
        'session_id' => 'CT' . $sessionId,
        'lead_id' => null,
        'referrer' => $faker->url,
        'domain' => $faker->domainName,
        'date_inquired' => NULL
    ];
});

$factory->define(TrackingUnit::class, static function (Faker $faker, array $attributes): array {
    // Get Dealer ID
    $dealer_id = $attributes['dealer_id'] ?? factory(User::class)->create()->getKey();

    // Get Dealer Location ID
    $dealer_location_id = $attributes['dealer_location_id'] ?? factory(DealerLocation::class)->create([
        'dealer_id' => $dealer_id
    ])->getKey();

    // Get Random Inventory Type
    $inventoryType = $faker->randomElement(['inventory', 'part', 'showroom']);
    switch($inventoryType) {
        case "part":
            $inventoryId = factory(Part::class)->create([
                'dealer_id' => $dealer_id
            ])->getKey();
        break;
        // TO DO: Support showroom? No factory exists yet...
        /*case "showroom":
            $inventoryId = factory(Showroom::class)->create()->getKey();
        break;*/
        default:
            $inventoryId = factory(Inventory::class)->create([
                'dealer_id' => $dealer_id,
                'dealer_location_id' => $dealer_location_id
            ])->getKey();
            $inventoryType = 'inventory';
        break;
    }

    // Get Session ID
    $sessionId = $attributes['session_id'] ?? factory(Tracking::class)->create()->session_id;

    // Return Array
    return [
        'session_id' => $sessionId,
        'inventory_id' => $inventoryId,
        'type' => $inventoryType,
        'referrer' => $faker->url,
        'path' => $faker->slug,
        'inquired' => 0
    ];
});