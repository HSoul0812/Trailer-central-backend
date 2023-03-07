<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CRM\User\EmailFolder;
use Faker\Generator as Faker;

$factory->define(EmailFolder::class, function (Faker $faker, array $attributes): array {
    return [
        'sales_person_id' => $attributes['sales_person_id'],
        'user_id' => $attributes['user_id'],
        'name' => $faker->word,
    ];
});
