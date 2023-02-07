<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CRM\Leads\Lead;
use App\Models\Website\Lead\WebsiteLeadFAndI;
use Faker\Generator as Faker;

$factory->define(WebsiteLeadFAndI::class, function (Faker $faker, array $attributes) {
    $data = [
        'lead_id' => factory(Lead::class)->create()->getKey(),
        'drivers_first_name' => $faker->firstName,
        'drivers_mid_name' => $faker->firstName,
        'drivers_last_name' => $faker->lastName,
        'drivers_suffix' => $faker->suffix,
        'drivers_dob' => $faker->dateTimeBetween('-50 years', '-10 years'),
        'drivers_no' => $faker->isbn10,
        'drivers_front' => '',
        'drivers_back' => '',
        'ssn_no' => $faker->creditCardNumber,
        'marital_status' => $faker->randomElement([
            WebsiteLeadFAndI::MARITAL_STATUS_SINGLE,
            WebsiteLeadFAndI::MARITAL_STATUS_MARRIED,
            WebsiteLeadFAndI::MARITAL_STATUS_DIVORCED,
            WebsiteLeadFAndI::MARITAL_STATUS_WIDOW,
        ]),
        'preferred_contact' => $faker->randomElement([
            WebsiteLeadFAndI::CONTACT_WAY_PHONE_DAYTIME,
            WebsiteLeadFAndI::CONTACT_WAY_PHONE_EVENING,
            WebsiteLeadFAndI::CONTACT_WAY_PHONE_MOBILE,
            WebsiteLeadFAndI::CONTACT_WAY_EMAIL,
        ]),
        'daytime_phone' => $faker->phoneNumber,
        'evening_phone' => $faker->phoneNumber,
        'mobile_phone' => $faker->phoneNumber,
        'rent_own' => $faker->randomElement(['rent', 'own']),
        'monthly_rent' => $faker->randomNumber(4),
        'type' => $faker->randomElement([
            WebsiteLeadFAndI::TYPE_SINGLE,
            WebsiteLeadFAndI::TYPE_JOINT,
            WebsiteLeadFAndI::TYPE_INDIVIDUAL,
        ]),
        'co_first_name' => $faker->firstName,
        'co_last_name' => $faker->lastName,
        'item_inquiry' => $faker->words(3, true),
        'item_price' => $faker->randomNumber(),
        'down_payment' => $faker->randomNumber(4),
        'trade_value' => $faker->randomNumber(4),
        'trade_payoff' => $faker->randomNumber(4),
        'other_income' => $faker->randomNumber(4),
        'other_income_source' => $faker->word,
        'extra' => $faker->words(2, true),
        'preferred_salesperson' => $faker->firstName,
        'delivery_method' => $faker->randomElement([
            WebsiteLeadFAndI::DELIVERY_METHOD_PICKUP,
            WebsiteLeadFAndI::DELIVERY_METHOD_RESIDENCE,
            WebsiteLeadFAndI::DELIVERY_METHOD_ELSEWHERE,
        ]),
        'date_imported' => $attributes['date_imported'] ?? $faker->dateTimeBetween('-2 years', '-30 days'),
    ];

    // Return Overrides
    return $data;
});
