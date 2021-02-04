<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadStatus;
use App\Models\CRM\Leads\LeadSource;
use App\Models\CRM\Leads\LeadType;
use App\Models\CRM\Leads\InventoryLead;
use App\Models\CRM\User\User;
use App\Models\CRM\User\SalesPerson;
use App\Models\Inventory\Inventory;
use Faker\Generator as Faker;

$factory->define(LeadStatus::class, function (Faker $faker) {
    $sales_person_id = $attributes['sales_person_id'] ?? factory(SalesPerson::class)->getKey();

    // Return Overrides
    return [
        'status' => $faker->randomElement(LeadStatus::STATUS_ARRAY),
        'contact_type' => LeadStatus::TYPE_CONTACT,
        'source' => $faker->company,
        'next_contact_date' => $faker->dateTimeBetween('now', '+1 month'),
        'sales_person_id' => $sales_person_id,
        'contact_type' => $faker->randomElement([LeadStatus::TYPE_CONTACT, LeadStatus::TYPE_TASK]),
    ];
});

$factory->define(LeadSource::class, function (Faker $faker) {
    $user_id = $attributes['user_id'] ?? factory(User::class)->getKey();

    // Return Overrides
    return [
        'user_id' => $user_id,
        'source' => $faker->company
    ];
});

$factory->define(LeadType::class, function (Faker $faker) {
    $lead_id = $attributes['lead_id'] ?? factory(Lead::class)->getKey();

    // Return Overrides
    return [
        'lead_id' => $lead_id,
        'lead_type' => $faker->randomElement(LeadType::TYPE_ARRAY)
    ];
});

$factory->define(InventoryLead::class, function (Faker $faker) {
    $lead_id = $attributes['lead_id'] ?? factory(Lead::class)->getKey();
    $inventory_id = $attributes['inventory_id'] ?? factory(Inventory::class)->getKey();

    // Return Overrides
    return [
        'lead_id' => $lead_id,
        'inventory_id' => $inventory_id
    ];
});