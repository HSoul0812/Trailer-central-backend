<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CRM\Email\Campaign;
use App\Models\CRM\Email\CampaignBrand;
use App\Models\CRM\Email\CampaignCategory;
use App\Models\CRM\Email\Template;
use App\Models\User\NewUser;
use Faker\Generator as Faker;

/**
 * Define Campaign Factory
 */
$factory->define(Campaign::class, function (Faker $faker, array $attributes) {
    $user_id = $attributes['user_id'] ?? factory(NewUser::class)->create->getKey();

    $template_id = $attributes['template_id'] ?? factory(Template::class)->create([
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