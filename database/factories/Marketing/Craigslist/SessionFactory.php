<?php

use App\Models\Marketing\Craigslist\Profile;
use App\Models\Marketing\Craigslist\Session;
use App\Models\User\User;
use Carbon\Carbon;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */

$factory->define(Session::class, static function (Faker $faker, array $attributes): array {
    // Get Dealer ID
    $user = isset($attributes['session_dealer_id']) ? null : factory(User::class)->create();
    $dealerId = $user ? $user->getKey() : $attributes['session_dealer_id'];

    // Get Profile ID
    $profile = isset($attributes['session_profile_id']) ? null : factory(Profile::class)->create(['dealer_id' => $dealer_id]);
    $profileId = $profile ? $profile->getKey() : $attributes['session_profile_id'];

    // Get UUID
    $uuid = $attributes['uuid'] ?? 'cr';
    while(strlen($uuid) < 16) {
        $uuid .= $faker->randomDigit();
    }

    // Configure Return Array
    return [
        'session_id' => $attributes['session_id'] ?? $faker->regexify('[A-Za-z0-9]{20}'),
        'session_client' => $attributes['session_client'] ?? $uuid,
        'session_scheduled' => $attributes['session_scheduled'] ?? Carbon::now()->toDateTimeString(),
        'session_started' => $attributes['session_started'] ?? Carbon::now()->toDateTimeString(),
        'session_confirmed' => $attributes['session_confirmed'] ?? Carbon::now()->toDateTimeString(),
        'session_dealer_id' => $dealerId,
        'session_slot_id' => $attributes['session_slot_id'] ?? $faker->randomNumber(2, true),
        'session_profile_id' => $profileId,
        'session_last_activity' => $attributes['session_last_activity'] ?? Carbon::now()->toDateTimeString(),
        'webui_last_activity' => $attributes['webui_last_activity'] ?? Carbon::now()->toDateTimeString(),
        'dispatch_last_activity' => $attributes['dispatch_last_activity'] ?? Carbon::now()->toDateTimeString(),
        'sound_notify' => $attributes['sound_notify'] ?? intval($faker->boolean),
        'recoverable' => $attributes['recoverable'] ?? 0,
        'status' => $attributes['status'] ?? 'pending-billing',
        'state' => $attributes['state'] ?? 'billing-add-funds',
        'text_status' => $attributes['text_status'] ?? 'Waiting for billing...',
        'nooped' => $attributes['nooped'] ?? 0,
        'nooped_until' => $attributes['nooped_until'] ?? 0,
        'queue_length' => $attributes['queue_length'] ?? 1,
        'last_item_began' => $attributes['last_item_began'] ?? 0,
        'log' => $attributes['log'] ?? '',
        'market_code' => $attributes['market_code'] ?? '',
        'prev_url' => $attributes['prev_url'] ?? $faker->url,
        'prev_url_skip' => $attributes['prev_url_skip'] ?? 0,
        'sync_page_count' => $attributes['sync_page_count'] ?? 0,
        'sync_current_page' => $attributes['sync_current_page'] ?? 0,
        'ajax_url' => $attributes['ajax_url'] ?? $faker->url,
        'notify_error_init' => $attributes['notify_error_init'] ?? 0,
        'notify_error_timeout' => $attributes['notify_error_timeout'] ?? 0,
        'dismissed' => $attributes['dismissed'] ?? 0,
        'tz_offset' => $attributes['session_confirmed'] ?? $faker->randomNumber(3),
        'total_cost' => $attributes['session_confirmed'] ?? $faker->randomFloat(2),
    ];
});