<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\Models\User\User;
use App\Models\User\DealerLocation;
use App\Models\Integration\Facebook\Page;
use App\Models\Integration\Facebook\Catalog;
use Faker\Generator as Faker;

/**
 * Define Catalog Factory
 */
$factory->define(Catalog::class, function (Faker $faker, array $attributes) {
    // Get Dealer ID
    $dealer_id = $attributes['dealer_id'] ?? factory(User::class)->create()->getKey();

    // Get Dealer Location ID
    $dealer_location_id = $attributes['dealer_location_id'] ?? factory(DealerLocation::class)->create([
        'dealer_id' => $dealer_id
    ])->getKey();

    // Get Page
    if(!empty($attributes['page_id'])) {
        $page = Page::where('page_id', $attributes['page_id']);
        $page_id = $page->getKey();
        $company = $page->title;
    } else {
        $page = factory(Page::class)->create();
        $page_id = $page->getKey();
        $company = $page->title;
    }

    // Return Overrides
    return [
        'dealer_id' => $dealer_id,
        'dealer_location_id' => $dealer_location_id,
        'fbapp_page_id' => $page_id,
        'business_id' => $faker->randomNumber(20, true),
        'catalog_id' => $faker->randomNumber(20, true),
        'catalog_name' => $company . "'s Catalog",
        'catalog_type' => $faker->randomElement(['vehicles', 'commerce']),
        'account_id' => $faker->randomNumber(20, true),
        'account_name' => $faker->name()
    ];
});

/**
 * Define Page Factory
 */
$factory->define(Page::class, function (Faker $faker, array $attributes) {
    // Get Dealer ID
    $dealer_id = $attributes['dealer_id'] ?? factory(User::class)->create()->getKey();

    // Return Overrides
    return [
        'dealer_id' => $dealer_id,
        'page_id' => $faker->randomNumber(20, true),
        'title' => $faker->company
    ];
});