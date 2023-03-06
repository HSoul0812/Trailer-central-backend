<?php

use App\Models\User\User;
use App\Models\Marketing\Craigslist\Profile;
use App\Models\Marketing\Craigslist\Session;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */

$factory->define(Session::class, static function (Faker $faker, array $attributes): array {
    // Get Dealer ID
    $user = isset($attributes['dealer_id']) ? null : factory(User::class)->create();
    $dealerId = $user ? $user->getKey() : $attributes['dealer_id'];

    // Get Profile ID
    $profile = isset($attributes['session_profile_id']) ? null : factory(Profile::class)->create();
    $profileId = $profile ? $profile->getKey() : $attributes['session_profile_id'];

    // Get Session ID
    $sessionId = $attributes['session_id'] ?? '';
    while(strlen($sessionId) < 32) {
        $letter = $faker->randomElement($faker->randomDigit(), strtoupper($faker->randomDigit()));
        $sessionId .= $faker->randomElement($faker->randomDigit(), $letter);
    }

    // Get UUID
    $uuid = $attributes['uuid'] ?? 'cr';
    while(strlen($uuid) < 16) {
        $uuid .= $faker->randomDigit();
    }

    // Configure Return Array
    return [
        'session_id' => $sessionId,
        'session_client' => $uuid,
        'session_scheduled' => $attributes['session_scheduled'] ?? Carbon::now()->toDateTimeString(),
        'session_started' => $attributes['session_started'] ?? Carbon::now()->toDateTimeString(),
        'session_confirmed' => $attributes['session_confirmed'] ?? Carbon::now()->toDateTimeString(),
        'session_dealer_id' => $dealerId,
        'session_slot_id' => $attributes['session_slot_id'] ?? 99,
        'session_profile_id' => $profileId,
        'session_last_activity' => $attributes['session_last_activity'] ?? Carbon::now()->toDateTimeString(),
        'webui_last_activity' => $attributes['webui_last_activity'] ?? Carbon::now()->toDateTimeString(),
        'dispatch_last_activity' => $attributes['dispatch_last_activity'] ?? Carbon::now()->toDateTimeString(),
        'sound_notify' => 0,
        'recoverable' => 0,
        'status' => $attributes['status'] ?? 'scheduled',
        'state' => $attributes['status'] ?? 'new',
        'text_status' => $attributes['text_status'] ?? 'Scheduled session created via factory.',
        'nooped' => 0,
        'nooped_until' => null,
        'queue_length' => 0,
        'last_item_began' => 0,
        'log' => '',
        'market_code' => '',
        'prev_url' => '',
        'prev_url_skip' => 0,
        'sync_page_count' => 0,
        'sync_current_page' => 0,
        'ajax_url' => '',
        'notify_error_init' => 0,
        'notify_error_timeout' => 0,
        'dismissed' => 0,
        'tz_offset' => 300
    ];
});