<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Tests\TestCase;
use App\Models\CRM\Text\Template;
use App\Models\User\NewDealerUser;
use Faker\Generator as Faker;

/**
 * Define Template Factory
 */
$factory->define(Template::class, function (Faker $faker) {
    // Get New Dealer User
    $newDealerUser = NewDealerUser::find(TestCase::getTestDealerId());

    // Return Overrides
    return [
        'user_id' => $newDealerUser->user_id,
        'name' => $faker->sentence,
        'template' => "Hello, {lead_name}\n\nWe see that you might be interested in {title_of_unit_of_interest}\n\nTesting, testing, 1, 2, 3!\n\nThank you!"
    ];
});