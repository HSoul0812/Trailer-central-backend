<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CRM\Interactions\TextLog;
use Faker\Generator as Faker;

$factory->define(TextLog::class, function (Faker $faker, array $attributes) {
    $leadId = $attributes['lead_id'] ?? 0;

    return [
        'log_message' => $faker->sentence,
        'from_number' => $faker->phoneNumber,
        'to_number' => $faker->phoneNumber,
        'lead_id' => $leadId,
        'date_sent' => new \DateTime()
    ];
});
