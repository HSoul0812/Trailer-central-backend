<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CRM\Leads\Jotform\WebsiteFormSubmissions;
use App\Models\CRM\Leads\Lead;
use Faker\Generator as Faker;

$factory->define(WebsiteFormSubmissions::class, function (Faker $faker, array $attributes) {
    $data = [
        'lead_id' => factory(Lead::class)->create()->getKey(),
        'merge_id' => $faker->randomNumber(4),
        'trade_id' => $faker->randomNumber(4),
        'submission_id' => mt_rand(),
        'jotform_id' => mt_rand(),
        'customer_id' => $attributes['customer_id'] ?? null,
        'ip_address' => $faker->ipv4,
        'created_at' => $attributes['created_at'] ?? now(),
        'updated_at' => $attributes['updated_at'] ?? now(),
        'status' => 'ACTIVE',
        'new' => true,
        'answers' => !empty($attributes['answers']) ? json_encode($attributes['answers']) : '',
        'is_ssn_removed' => false,
    ];

    // Return Overrides
    return $data;
});
