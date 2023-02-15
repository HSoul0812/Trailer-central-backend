<?php

use App\Models\Marketing\Craigslist\Category;
use App\Models\Marketing\Craigslist\Market;
use App\Models\User\User;
use App\Models\User\DealerLocation;
use App\Models\Marketing\Craigslist\Profile;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(Profile::class, static function (Faker $faker, array $attributes): array {
    // Get Dealer
    $user = isset($attributes['dealer_id']) ? null : factory(User::class)->create();
    $userId = $user ? $user->getKey() : $attributes['dealer_id'];

    // Get Dealer Location
    $dealerLocation = isset($attributes['dealer_location_id']) ? null : factory(DealerLocation::class)->create();
    $dealerLocationId = $dealerLocation ? $dealerLocation->getKey() : $attributes['dealer_location_id'];

    // Get Random Posting Category
    $category = !isset($attributes['postCategory']) ? Category::where('grouping', 'fsd')->inRandomOrder()->first() : null;
    $postCategory = $category ? $category->category : $attributes['postCategory'];

    // Get Random Market
    if (isset($attributes['market_city']) && isset($attributes['market_subarea'])) {
        $market = Market::where('city_code', $attributes['market_city'])->where('subarea_code', $attributes['market_subarea'])->first();
    } elseif (isset($attributes['market_city'])) {
        $market = Market::where('city_code', $attributes['market_city'])->first();
    } else {
        $market = Market::inRandomOrder()->first();
    }

    // Configure Return Array
    return [
        'dealer_id' => $userId,
        'dealer_location_id' => $dealerLocationId,
        'location_filter' => 'All',
        'username' => isset($attributes['username']) ? $attributes['username'] : $faker->username,
        'password' => isset($attributes['password']) ? $attributes['password'] : $faker->password,
        'profile' => isset($attributes['profile']) ? $attributes['profile'] : $faker->company,
        'phone' => isset($attributes['phone']) ? $attributes['phone'] : $faker->phoneNumber,
        'location' => isset($attributes['location']) ? $attributes['location'] : $faker->city,
        'postal' => isset($attributes['postal']) ? $attributes['postal'] : $faker->postcode,
        'city' => $market->city_name,
        'city_location' => $market->subarea_name,
        'postCategory' => $postCategory,
        'postingInterval' => 0,
        'cl_privacy' => isset($attributes['cl_privacy']) ? $attributes['cl_privacy'] : 'C',
        'image_limit' => isset($attributes['image_limit']) ? (int) $attributes['image_limit'] : 24,
        'renew_interval' => 0,
        'use_map' => isset($attributes['use_map']) ? (int) $attributes['use_map'] : 1,
        'map_street' => isset($attributes['map_street']) ? $attributes['map_street'] : $faker->streetAddress,
        'map_cross_street' => isset($attributes['map_cross_street']) ? $attributes['map_cross_street'] : $faker->streetAddress,
        'map_city' => isset($attributes['map_city']) ? $attributes['map_city'] : $faker->city,
        'map_state' => isset($attributes['map_state']) ? $attributes['map_state'] : $faker->state,
        'format_dbk' => isset($attributes['format_dbk']) ? (int) $attributes['format_dbk'] : 1,
        'format_dfbk' => isset($attributes['format_dfbk']) ? (int) $attributes['format_dfbk'] : 0,
        'format_fbk' => isset($attributes['format_fbk']) ? (int) $attributes['format_fbk'] : 0,
        'show_more_ads' => isset($attributes['show_more_ads']) ? (int) $attributes['show_more_ads'] : 0,
        'autoposting_enable' => isset($attributes['autoposting_enable']) ? (int) $attributes['autoposting_enable'] : 0,
        'autoposting_items' => isset($attributes['autoposting_items']) ? (int) $attributes['autoposting_items'] : 1,
        'autoposting_hrs' => isset($attributes['autoposting_hrs']) ? (int) $attributes['autoposting_hrs'] : 8,
        'autoposting_slot_id' => isset($attributes['autoposting_slot_id']) ? (int) $attributes['autoposting_slot_id'] : 1,
        'autoposting_start_at' => isset($attributes['autoposting_start_at']) ? (int) $attributes['autoposting_start_at'] : 0,
        'embed_phone' => isset($attributes['embed_phone']) ? (int) $attributes['embed_phone'] : 0,
        'embed_dealer_and_phone' => isset($attributes['embed_dealer_and_phone']) ? (int) $attributes['embed_dealer_and_phone'] : 0,
        'embed_logo' => isset($attributes['embed_logo']) ? (int) $attributes['embed_logo'] : 0,
        'embed_logo_position' => isset($attributes['embed_logo_position']) ? $attributes['embed_logo_position'] : 'none',
        'embed_logo_width' => isset($attributes['embed_logo_width']) ? (int) $attributes['embed_logo_width'] : 0,
        'embed_logo_height' => isset($attributes['embed_logo_height']) ? (int) $attributes['embed_logo_height'] : 0,
        'embed_upper' => isset($attributes['embed_upper']) ? $attributes['embed_upper'] : 'dealer',
        'embed_bg_upper' => isset($attributes['embed_bg_upper']) ? $attributes['embed_bg_upper'] : '#000000',
        'embed_text_upper' => isset($attributes['embed_text_upper']) ? $attributes['embed_text_upper'] : '#ffffff',
        'embed_lower' => isset($attributes['embed_lower']) ? $attributes['embed_lower'] : 'phone',
        'embed_bg_lower' => isset($attributes['embed_bg_lower']) ? $attributes['embed_bg_lower'] : '#000000',
        'embed_text_lower' => isset($attributes['embed_text_lower']) ? $attributes['embed_text_lower'] : '#ffffff',
        'keywords' => isset($attributes['keywords']) ? $attributes['keywords'] : $faker->words(12, true),
        'scramble' => isset($attributes['scramble']) ? (int) $attributes['scramble'] : 0,
        'blurb' => isset($attributes['blurb']) ? $attributes['blurb'] : $faker->paragraph,
        'proxy_type' => 0,
        'proxy_host' => 0,
        'proxy_port' => 0,
        'proxy_user' => 0,
        'proxy_pass' => 0,
        'sound_notify' => 1,
        'use_website_price' => isset($attributes['use_website_price']) ? (int) $attributes['use_website_price'] : 0,
        'market_city' => $market->city_code,
        'market_subarea' => $market->subarea_code,
        'profile_type' => isset($attributes['profile_type']) ? $attributes['profile_type'] : 'inventory'
    ];
});
