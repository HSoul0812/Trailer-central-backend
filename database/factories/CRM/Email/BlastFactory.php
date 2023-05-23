<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Email\Blast;
use App\Models\CRM\Email\BlastSent;
use App\Models\CRM\Email\BlastBrand;
use App\Models\CRM\Email\BlastCategory;
use App\Models\CRM\Email\Template;
use App\Models\User\NewUser;
use Faker\Generator as Faker;
use Carbon\Carbon;
use App\Models\CRM\Interactions\EmailHistory;

/**
 * Define Blast Factory
 */
$factory->define(Blast::class, function (Faker $faker, array $attributes) {
    $user_id = $attributes['user_id'] ?? factory(NewUser::class)->create()->getKey();

    $template_id = $attributes['template_id'] ?? factory(Template::class)->create([
        'user_id' => $user_id
    ])->getKey();

    // Return Overrides
    return [
        'user_id' => $user_id,
        'email_template_id' => $template_id,
        'campaign_name' => $faker->sentence,
        'from_email_address' => $faker->email,
        'action' => 'inquired',
        'send_after_days' => 15,
        'send_date' => Carbon::now()->subDay()->toDateTimeString()
    ];
});

/**
 * Define Blast Sent Factory
 */
$factory->define(BlastSent::class, function(Faker $faker, array $attributes) {
    $email_blasts_id = $attributes['email_blasts_id'] ?? factory(Blast::class)->create()->getKey();

    $lead_id = $attributes['lead_id'] ?? factory(Lead::class)->create()->getKey();
    $now = Carbon::now();

    $messageId = '<' . $faker->md5 . '@' . $faker->freeEmailDomain . '>';

    // Return Overrides
    return [
        'email_blasts_id' => $email_blasts_id,
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
 * Define Blast Brand Factory
 */
$factory->define(BlastBrand::class, function (Faker $faker) {
    return [];
});

/**
 * Define Blast Category Factory
 */
$factory->define(BlastCategory::class, function (Faker $faker) {
    return [];
});