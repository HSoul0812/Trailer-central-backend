<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadTrade;
use App\Models\CRM\Leads\LeadTradeImage;
use Faker\Generator as Faker;

$factory->define(LeadTrade::class, function (Faker $faker, array $attributes) {
    $leadId = $attributes['lead_id'] ?? factory(Lead::class)->create()->getKey();

    return [
        'lead_id' => $leadId,
        'type' => $faker->title,
        'make' => $faker->company,
        'model' => $faker->companySuffix,
        'year' => $faker->year,
        'price' => $faker->randomDigit,
        'length' => $faker->randomDigit,
        'width' => $faker->randomDigit,
        'notes' => $faker->text,
    ];
});

$factory->define(LeadTradeImage::class, function (Faker $faker, array $attributes) {
    $leadTradeId = $attributes['trade_id'] ?? factory(LeadTrade::class)->create()->getKey();

    return [
        'trade_id' => $leadTradeId,
        'filename' => $faker->title,
        'path' => $faker->url,
    ];
});
