<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Tests\TestCase;
use App\Models\CRM\Text\Campaign;
use App\Models\User\NewDealerUser;
use Faker\Generator as Faker;

/**
 * Define Campaign Factory
 */
$factory->define(Campaign::class, function (Faker $faker) {
    // Get New Dealer User
    $dealer = NewDealerUser::find(TestCase::getTestDealerId());

    // Get Template ID
    $template = Template::where('user_id', $dealer->user_id)->inRandomOrder()->first();
    
    // Get Name
    $name = $faker->sentence;

    // Return Overrides
    return [
        'user_id' => $dealer->user_id,
        'template_id' => $template->id,
        'campaign_name' => $name,
        'campaign_subject' => $name,
        'from_sms_number' => TestCase::getSMSNumber(),
        'send_after_days' => 15,
        'action' => 'inquired'
    ];
});

/**
 * Define Campaign Brand Factory
 */
$factory->define(CampaignBrand::class, function (Faker $faker) {
    return [];
});

/**
 * Define Campaign Category Factory
 */
$factory->define(CampaignCategory::class, function (Faker $faker) {
    return [];
});