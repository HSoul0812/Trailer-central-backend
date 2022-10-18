<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Tests\TestCase;
use App\Models\CRM\Text\Template;
use App\Models\User\NewDealerUser;
use Faker\Generator as Faker;

/**
 * Define Template Factory
 */
$factory->define(Template::class, function (Faker $faker, array $attributes) {
    $userId = $attributes['user_id'] ?? NewDealerUser::find(TestCase::getTestDealerId())->user_id;

    return [
        'user_id' => $userId,
        'name' => $faker->sentence,
        'template' => "Hello, {lead_name}\n\nWe see that you might be interested in {title_of_unit_of_interest}\n\nTesting, testing, 1, 2, 3!\n\nThank you!"
    ];
});
