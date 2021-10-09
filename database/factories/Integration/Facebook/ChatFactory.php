<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\Models\User\NewUser;
use App\Models\CRM\User\SalesPerson;
use App\Models\User\DealerLocation;
use App\Models\Integration\Facebook\Page;
use App\Models\Integration\Facebook\Catalog;
use Faker\Generator as Faker;

/**
 * Define Catalog Factory
 */
$factory->define(Catalog::class, function (Faker $faker, array $attributes) {
    // Get Dealer ID
    $user_id = $attributes['dealer_id'] ?? factory(NewUser::class)->create()->getKey();

    // Get Dealer Location ID
    $dealer_location_id = $attributes['dealer_location_id'] ?? factory(DealerLocation::class)->create([
        'user_id' => $user_id
    ])->getKey();

    // Get Sales Person ID
    $sales_person_id = $attributes['sales_person_id'] ?? factory(SalesPerson::class)->create([
        'user_id' => $user_id,
        'dealer_location_id' => $dealer_location_id
    ])->getKey();

    // Get Page
    if(!empty($attributes['page_id'])) {
        $page_id = Page::where('page_id', $attributes['page_id'])->getKey();
    } else {
        $page_id = factory(Page::class)->create()->getKey();
    }

    // Return Overrides
    return [
        'user_id' => $user_id,
        'sales_person_id' => $sales_person_id,
        'account_id' => $faker->randomNumber(9, true) . $faker->randomNumber(9, true),
        'account_name' => $faker->name(),
        'page_id' => $page_id
    ];
});