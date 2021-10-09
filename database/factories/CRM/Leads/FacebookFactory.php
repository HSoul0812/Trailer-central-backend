<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CRM\Leads\Facebook\User as FbUser;
use App\Models\CRM\Leads\Facebook\Lead as FbLead;
use App\Models\Integration\Facebook\Page;
use App\Models\User\User;
use Faker\Generator as Faker;

$factory->define(FbUser::class, function (Faker $faker, array $attributes) {
    // Return Overrides
    return [
        'user_id' => $faker->randomNumber(20, true),
        'name' => $attributes['name'] ?? $faker->name(),
        'email' => $attributes['email'] ?? $faker->safeEmail
    ];
});

$factory->define(FbLead::class, function (Faker $faker, array $attributes) {
    // Get Page Id
    if(!empty($attributes['page_id'])) {
        $page_id = $attributes['page_id'];
    } else {
        // Get Dealer ID
        $dealer_id = $attributes['dealer_id'] ?? factory(User::class)->create()->getKey();

        // Get Page
        $page_id = factory(Page::class)->create([
            'dealer_id' => $dealer_id
        ])->page_id;
    }

    // Get Lead
    if(!empty($attributes['lead_id'])) {
        $lead = Lead::find($attributes['lead_id']);
    } else {
        if(!empty($attributes['user_id'])) {
            $user = FbUser::where('user_id', $attributes['user_id'])->first();
            $fill = [
                'name' => $user->name,
                'email' => $user->email
            ];
        }

        // Return 
        $lead = factory(Lead::class)->create($fill ?? []);
    }

    // Get User
    $user_id = $attributes['user_id'] ?? factory(FbUser::class)->create([
        'name' => $lead->full_name,
        'email' => $lead->email_address
    ])->user_id;

    // Return Overrides
    return [
        'page_id' => $page_id,
        'user_id' => $user_id,
        'lead_id' => $lead->identifier,
        'merge_id' => $attributes['merge_id'] ?? 0
    ];
});