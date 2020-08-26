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
    $newDealerUser = NewDealerUser::find(TestCase::getTestDealerId());

    // Get Template ID
    $template = Template::where('user_id', TestCase::getTestDealerId())->inRandomOrder()->first();
    
    // Get Name
    $name = $faker->title;

    // Return Overrides
    return [
        'user_id' => $newDealerUser->user_id,
        'template_id' => !empty($template->id) ? $template->id : 0,
        'campaign_name' => $name,
        'campaign_subject' => $name,
        'from_sms_number' => $faker->phoneNumber,
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