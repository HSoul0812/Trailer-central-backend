<?php

/** @var Factory $factory */

use App\Models\User\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use \App\Models\Bulk\Inventory\BulkUpload;

$factory->define(BulkUpload::class, static function (Faker $faker, array $attributes): array {

    $date = $faker->dateTimeThisMonth->format('Y-m-d H:i:s');

    $fileName = $attributes['import_source'] ?? sprintf(
        '%s/%s/%s.txt', $faker->slug(2), $faker->slug(2), $faker->uuid
    );

    return [
        'id' => $attributes['id'] ?? null,
        'dealer_id' => $attributes['dealer_id'] ?? factory(User::class)->create()->getKey(),
        'title' => $faker->slug(2),
        'status' => $attributes['status'] ?? 'complete', // for now we dont need to randomize it
        'import_source' => $fileName,
        'validation_errors' => '',
        'created_at' => $date,
        'updated_at' => $date
    ];
});
