<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CRM\Email\Template;
use App\Models\User\NewUser;
use Faker\Generator as Faker;

/**
 * Define Template Factory
 */
$factory->define(Template::class, function (Faker $faker, array $attributes) {
    $user_id = $attributes['user_id'] ?? factory(NewUser::class)->create->getKey();

    $name = $attributes['name'] ?? 'versafix-1';

    // Return Overrides
    return [
        'user_id' => $user_id,
        'name' => $name,
        'html' => "Hello, {lead_name}\n\nWe see that you might be interested in {title_of_unit_of_interest}\n\nTesting, testing, 1, 2, 3!\n\nThank you!",
        'template' => 'templates/' . $name . '/template-' . $name . '.html',
        'template_key' => $faker->uuid
    ];
});