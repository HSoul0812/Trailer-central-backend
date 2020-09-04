<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

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
$factory->define(Blast::class, function (Faker $faker) {
    // Get New Dealer User
    $dealer = NewDealerUser::find(TestCase::getTestDealerId());

    // Get Template ID
    $template = Template::where('user_id', $dealer->user_id)->inRandomOrder()->first();

    // Get Name
    $name = $faker->sentence;

    // Return Overrides
    return [
        'user_id' => $dealer->user_id,
        'template_id' => !empty($template->id) ? $template->id : 0,
        'campaign_name' => $name,
        'campaign_subject' => $name,
        'from_sms_number' => TestCase::getSMSNumber(),
        'action' => 'inquired',
        'send_after_days' => 15,
        'send_date' => Carbon::subDay()->toDateTimeString()
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