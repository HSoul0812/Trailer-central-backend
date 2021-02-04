<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadStatus;
use App\Models\CRM\Leads\LeadSource;
use App\Models\CRM\Leads\LeadType;
use App\Models\CRM\Leads\InventoryLead;
use App\Models\CRM\User\SalesPerson;
use App\Models\Inventory\Inventory;
use App\Models\User\User;
use Faker\Generator as Faker;

$factory->define(LeadStatus::class, function (Faker $faker) {
    $sales_person_id = $attributes['sales_person_id'] ?? factory(SalesPerson::class)->create()->getKey();

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
    $user_id = $attributes['user_id'] ?? factory(User::class)->create()->getKey();

    // Return Overrides
    return [
        'user_id' => $user_id,
        'source_name' => $faker->company
    ];
});

$factory->define(LeadType::class, function (Faker $faker) {
    $lead_id = $attributes['lead_id'] ?? factory(Lead::class)->create()->getKey();

    // Return Overrides
    return [
        'lead_id' => $lead_id,
        'lead_type' => $faker->randomElement(LeadType::TYPE_ARRAY)
    ];
});

$factory->define(InventoryLead::class, function (Faker $faker) {
    $inventory = factory(Inventory::class)->create();
    $inventory_id = $attributes['inventory_id'] ?? $inventory->getKey();
    $lead_id = $attributes['lead_id'] ?? factory(Lead::class)->create([
        'dealer_id' => $inventory->dealer_id,
        'inventory_id' => $inventory_id
    ])->getKey();

    // Return Overrides
    return [
        'lead_id' => $lead_id,
        'inventory_id' => $inventory_id
    ];
});