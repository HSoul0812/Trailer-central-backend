<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Email\Campaign;
use App\Models\CRM\Email\CampaignSent;
use App\Models\CRM\Email\CampaignBrand;
use App\Models\CRM\Email\CampaignCategory;
use App\Models\CRM\Email\Template;
use App\Models\User\NewUser;
use Faker\Generator as Faker;
use Carbon\Carbon;
use App\Models\CRM\Interactions\EmailHistory;

/**
 * Define Campaign Factory
 */
$factory->define(Campaign::class, function (Faker $faker, array $attributes) {
    $user_id = $attributes['user_id'] ?? factory(NewUser::class)->create()->getKey();

    $template_id = $attributes['email_template_id'] ?? factory(Template::class)->create([
        'user_id' => $user_id
    ])->getKey();

    // Return Overrides
    return [
        'user_id' => $user_id,
        'email_template_id' => $template_id,
        'campaign_name' => $faker->sentence,
        'from_email_address' => $faker->email,
        'send_after_days' => 15,
        'action' => 'inquired'
    ];
});

/**
 * Define Campaign Sent Factory
 */
$factory->define(CampaignSent::class, function(Faker $faker, array $attributes) {
    $drip_campaigns_id = $attributes['drip_campaigns_id'] ?? factory(Campaign::class)->create()->getKey();

    $lead_id = $attributes['lead_id'] ?? factory(Lead::class)->create()->getKey();
    $now = Carbon::now();

    $messageId = '<' . $faker->md5 . '@' . $faker->freeEmailDomain . '>';

    // Return Overrides
    return [
        'drip_campaigns_id' => $drip_campaigns_id,
        'lead_id' => $lead_id,
        'message_id' => $messageId,
        'crm_email_history_id' => $attributes['crm_email_history_id'] ?? factory(EmailHistory::class)->create([
            'message_id' => $messageId,
            'date_sent' => $now,
            'date_delivered' => $now,
            'date_bounced' => $now,
            'date_complained' => $now,
            'date_unsubscribed' => $now,
            'date_opened' => $now,
            'date_clicked' => $now,
            'was_skipped' => 1,
            'invalid_email' => 1,
        ])->getKey()
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
