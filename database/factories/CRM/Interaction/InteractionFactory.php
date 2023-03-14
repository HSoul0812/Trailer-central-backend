<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Tests\TestCase;
use App\Models\CRM\Interactions\Interaction;
use Faker\Generator as Faker;

$factory->define(Interaction::class, function (Faker $faker, array $attributes = []) {
    // Return Overrides
    return [
        'tc_lead_id' => $attributes['tc_lead_id'] ?? 0,
        'user_id' => $attributes['user_id'] ?? TestCase::getTestDealerId(),
        'sales_person_id' => $attributes['sales_person_id'] ?? NULL,
        'interaction_type' => $attributes['interaction_type'] ?? 'EMAIL',
        'interaction_time' => $faker->dateTimeThisMonth,
        'interaction_notes' => $attributes['interaction_notes'] ?? ''
    ];
});