<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadStatus;
use App\Models\CRM\Leads\LeadSource;
use App\Models\CRM\Leads\LeadType;
use App\Models\CRM\Leads\InventoryLead;
use App\Models\Inventory\Inventory;
use App\Models\User\User;
use Faker\Generator as Faker;

$factory->define(LeadStatus::class, function (Faker $faker, array $attributes) {
    $lead_id = $attributes['tc_lead_identifier'] ?? factory(Lead::class)->create()->getKey();
    $sales_person_id = $attributes['sales_person_id'] ?? 0;
    $status = $attributes['status'] ?? $faker->randomElement(LeadStatus::STATUS_ARRAY);

    // Return Overrides
    return [
        'tc_lead_identifier' => $lead_id,
        'status' => $status,
        'source' => $faker->company,
        'next_contact_date' => $faker->dateTimeBetween('now', '+1 month')->format('Y-m-d H:i:s'),
        'sales_person_id' => $sales_person_id,
        'contact_type' => $faker->randomElement([LeadStatus::TYPE_CONTACT, LeadStatus::TYPE_TASK]),
    ];
});

$factory->define(LeadSource::class, function (Faker $faker, array $attributes) {
    $user_id = $attributes['user_id'] ?? factory(User::class)->create()->getKey();

    // Return Overrides
    return [
        'user_id' => $user_id,
        'source_name' => $faker->company
    ];
});

$factory->define(LeadType::class, function (Faker $faker, array $attributes) {
    $lead_id = $attributes['lead_id'] ?? factory(Lead::class)->create()->getKey();

    // Return Overrides
    return [
        'lead_id' => $lead_id,
        'lead_type' => $faker->randomElement(LeadType::TYPE_ARRAY)
    ];
});

$factory->define(InventoryLead::class, function (Faker $faker, array $attributes) {
    // Get Inventory By Default
    if(!empty($attributes['inventory_id'])) {
        $inventory = Inventory::find($attributes['inventory_id']);
    } else {
        $inventory = factory(Inventory::class)->create();
    }
    $inventory_id = $attributes['inventory_id'] ?? $inventory->getKey();

    // Get Lead
    $lead_id = $attributes['website_lead_id'] ?? factory(Lead::class)->create([
        'dealer_id' => $inventory->dealer_id,
        'inventory_id' => $inventory_id
    ])->getKey();

    // Return Overrides
    return [
        'website_lead_id' => $lead_id,
        'inventory_id' => $inventory_id
    ];
});
