<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CRM\Documents\DealerDocuments;
use App\Models\CRM\Leads\Lead;
use App\Models\User\User;
use Faker\Generator as Faker;

$factory->define(DealerDocuments::class, function (Faker $faker, array $attributes) {
    $dealerId = $attributes['dealer_id'] ?? factory(User::class)->create()->getKey();
    $leadId = $attributes['lead_id'] ?? factory(Lead::class)->create(['dealer_id' => $dealerId])->getKey();

    return [
        'lead_id' => $leadId,
        'dealer_id' => $dealerId,
        'filename' => $faker->title,
        'full_path' => $faker->url,
        'docusign_path' => $faker->title,
        'docusign_data' => $faker->text,
    ];
});
