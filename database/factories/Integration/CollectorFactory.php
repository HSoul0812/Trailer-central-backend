<?php

/** @var Factory $factory */

use App\Models\Integration\Collector\Collector;
use App\Models\User\DealerLocation;
use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;
use App\Models\User\User;

$factory->define(Collector::class, function (Faker $faker, array $attributes) {
    $dealer_id = $attributes['dealer_id'] ?? factory(User::class)->create()->getKey();

    $dealer_location_id = $attributes['dealer_location_id'] ?? factory(DealerLocation::class)->create([
            'dealer_id' => $dealer_id
        ])->getKey();

    return [
        'dealer_id' => $dealer_id,
        'dealer_location_id' => $dealer_location_id,
        'process_name' => 'process_' . substr(md5(mt_rand()), 0, 7),
        'ftp_host' => $faker->name,
        'ftp_path' => $faker->name,
        'ftp_login' => $faker->name,
        'ftp_password' => $faker->password,
        'file_format' => 'csv',
        'title_format' => '',
    ];
});
