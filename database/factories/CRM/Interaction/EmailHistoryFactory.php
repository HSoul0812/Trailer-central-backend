<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CRM\Interactions\Interaction;
use App\Models\CRM\Interactions\EmailHistory;
use Faker\Generator as Faker;

$factory->define(EmailHistory::class, function (Faker $faker, array $attributes) {
    $leadId = $attributes['lead_id'] ?? 0;

    // Return Overrides
    return [
        'lead_id' => $leadId,
        'interaction_id' => function (array $email) {
            return factory(Interaction::class)->make([
                'tc_lead_id' => $email['lead_id'],
                'interaction_notes' => 'E-Mail Sent: ' . $email['subject'],
                'interaction_time' => $email['date_sent']
            ]);
        },
        'message_id' => $attributes['message_id'] ?? '<' . $faker->md5 . '@' . $faker->freeEmailDomain . '>',
        'to_email' => $faker->email,
        'to_name' => $faker->name,
        'from_email' => $faker->companyEmail,
        'from_name' => $faker->name,
        'subject' => $faker->sentence,
        'body' => $faker->randomHtml(2,3),
        'use_html' => 1,
        'date_sent' => $faker->dateTimeThisMonth
    ];
});
