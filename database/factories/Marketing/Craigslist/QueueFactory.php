<?php

use App\Models\Inventory\Inventory;
use App\Models\Parts\Part;
use App\Models\Marketing\Craigslist\Queue;
use App\Models\Marketing\Craigslist\Profile;
use App\Models\User\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(Queue::class, static function (Faker $faker, array $attributes): array {
    // Get Dealer ID
    $user = isset($attributes['dealer_id']) ? null : factory(User::class)->create();
    $dealerId = $user ? $user->getKey() : $attributes['dealer_id'];

    // Get Inventory ID
    if(!empty($attributes['parameter']['type']) && $attributes['parameter']['type'] === 'parts') {
        $inventory = isset($attributes['inventory_id']) ? null : factory(Part::class)->create([
            'dealer_id' => $dealerId
        ]);
        $inventoryId = $inventory ? $inventory->getKey() : $attributes['inventory_id'];
    } else {
        $inventory = isset($attributes['inventory_id']) ? null : factory(Inventory::class)->create([
            'dealer_id' => $dealerId
        ]);
        $inventoryId = $inventory ? $inventory->getKey() : $attributes['inventory_id'];
    }

    // Get Profile ID
    $profile = isset($attributes['profile_id']) ? null : factory(Profile::class)->create();
    $profileId = $profile ? $profile->getKey() : $attributes['profile_id'];

    // Get Parameters
    $parameters = $attributes['parameter'] ?? [];
    if(empty($parameters)) {
        $images = [];
        foreach($inventory->orderedImages as $image) {
            $images[] = config('services.aws.url') . '/' . $image->image->filename;
        }

        // Create Parameters JSON
        $parameters = json_encode([
            'type' => $attributes['parameter']['type'] ?? 'inventory',
            'price' => $inventory->price,
            'location' => $profile->location,
            'postCategory' => $profile->category->id,
            'body' => $inventory->description_html,
            'title' => $inventory->title,
            'contact_name' => $profile->user->name,
            'phone' => $profile->phone,
            'postal' => $profile->postal,
            'make' => $inventory->manufacturer,
            'model' => $inventory->model,
            'size' => '',
            'images' => $images
        ]);
    } elseif(is_array($parameters)) {
        $parameters = json_encode($parameters);
    }

    // Get Session ID
    $sessionId = $attributes['session_id'] ?? $faker->regexify('[A-Za-z0-9]{20}');

    // Configure Return Array
    return [
        'session_id' => $attributes['session_id'] ?? $sessionId,
        'parent_id' => $attributes['parent_id'] ?? 0,
        'time' => $attributes['time'] ?? time(),
        'command' => $attributes['command'] ?? 'postAdd',
        'parameter' => $attributes['parameter'] ?? $parameters,
        'dealer_id' => $dealerId,
        'profile_id' => $profileId,
        'inventory_id' => $inventoryId,
        'status' => $attributes['status'] ?? 'unprocessed',
        'state' => $attributes['state'] ?? 'new',
        'img_state' => $attributes['img_state'] ?? '',
        'costs' => $attributes['costs'] ?? 0,
        'log' => $attributes['log'] ?? ''
    ];
});