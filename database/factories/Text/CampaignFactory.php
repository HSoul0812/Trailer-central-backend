<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Tests\TestCase;
use App\Models\CRM\Text\Campaign;
use App\Models\CRM\Text\CampaignBrand;
use App\Models\CRM\Text\CampaignCategory;
use App\Models\CRM\Text\Template;
use App\Models\User\NewDealerUser;
use Faker\Generator as Faker;

/**
 * Define Campaign Factory
 */
$factory->define(Campaign::class, function (Faker $faker, array $attributes) {
    // Get New Dealer User
    if (!isset($attributes['user_id'])) {
        $dealer = NewDealerUser::find(TestCase::getTestDealerId());
        $attributes['user_id'] = $dealer->user_id;
    }

    // Get Template ID
    $template = Template::where('user_id', $attributes['user_id'])->inRandomOrder()->first();
   
    // Get Name
    $name = $faker->sentence;

    // Return Overrides
    return [
        'user_id' => $attributes['user_id'],
        'template_id' => !empty($template->id) ? $template->id : 0,
        'campaign_name' => $name,
        'from_sms_number' => $attributes['from_sms_number'] ?? TestCase::getSMSNumber(),
        'send_after_days' => 15,
        'action' => $attributes['action'] ?? 'inquired'
    ];
});

/**
 * Define Campaign Sent Factory
 */
$factory->define(CampaignSent::class, function(Faker $faker, array $attributes) {
    $drip_campaigns_id = $attributes['drip_campaigns_id'] ?? factory(Campaign::class)->create->getKey();

    $lead_id = $attributes['lead_id'] ?? factory(Lead::class)->create->getKey();

    // Return Overrides
    return [
        'drip_campaigns_id' => $drip_campaigns_id,
        'lead_id' => $lead_id,
        'message_id' => '<' . $faker->md5 . '@' . $faker->freeEmailDomain . '>'
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