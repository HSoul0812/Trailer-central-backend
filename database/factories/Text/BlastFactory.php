<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Text\BlastSent;
use Tests\TestCase;
use App\Models\CRM\Text\Blast;
use App\Models\CRM\Text\BlastBrand;
use App\Models\CRM\Text\BlastCategory;
use App\Models\CRM\Text\Template;
use App\Models\User\NewDealerUser;
use Faker\Generator as Faker;
use Carbon\Carbon;

/**
 * Define Blast Factory
 */
$factory->define(Blast::class, function (Faker $faker, array $attributes) {
    $userId = $attributes['user_id'] ?? NewDealerUser::find(TestCase::getTestDealerId())->user_id;
    $campaignName = $attributes['campaign_name'] ?? $faker->sentence;
    $fromSmsNumber = $attributes['from_sms_number'] ?? TestCase::getSMSNumber();
    $action = $attributes['action'] ?? 'inquired';
    $isDelivered = $attributes['is_delivered'] ?? false;
    $sendAfterDays = $attributes['send_after_days'] ?? 15;
    $locationId = $attributes['location_id'] ?? null;

    if (!empty($attributes['template_id'])) {
        $templateId = $attributes['template_id'];
    } else {
        $template = Template::where('user_id', $userId)->inRandomOrder()->first();
        $templateId = !empty($template->id) ? $template->id : 0;
    }

    return [
        'user_id' => $userId,
        'template_id' => $templateId,
        'campaign_name' => $campaignName,
        'from_sms_number' => $fromSmsNumber,
        'action' => $action,
        'send_after_days' => $sendAfterDays,
        'send_date' => Carbon::now()->subDay()->toDateTimeString(),
        'is_delivered' => $isDelivered,
        'is_cancelled' => false,
        'deleted' => false,
        'location_id' => $locationId,
    ];
});

/**
 * Define Blast Sent Factory
 */
$factory->define(BlastSent::class, function(Faker $faker, array $attributes) {
    $textBlastId = $attributes['text_blast_id'] ?? factory(Blast::class)->create()->getKey();
    $leadId = $attributes['lead_id'] ?? factory(Lead::class)->create()->getKey();

    return [
        'text_blast_id' => $textBlastId,
        'lead_id' => $leadId,
        'text_id' => $faker->randomDigit,
        'status' => 'sent',
    ];
});

/**
 * Define Blast Brand Factory
 */
$factory->define(BlastBrand::class, function (Faker $faker, array $attributes) {
    return [
        'text_blast_id' => $attributes['text_blast_id'],
        'brand' => $attributes['brand'],
    ];
});

/**
 * Define Blast Category Factory
 */
$factory->define(BlastCategory::class, function (Faker $faker, array $attributes) {
    return [
        'text_blast_id' => $attributes['text_blast_id'],
        'category' => $attributes['category'],
    ];
});
