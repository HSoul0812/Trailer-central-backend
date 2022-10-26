<?php

/** @var Factory $factory */

use App\Models\Parts\Bin;
use App\Models\Parts\BinQuantity;
use App\Models\Parts\Part;
use App\Models\User\DealerLocation;
use App\Models\User\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(BinQuantity::class, static function (Faker $faker, array $attributes): array {
    return [
        'part_id' => factory(Part::class)->create()->id,
        'bin_id' => factory(Bin::class)->create()->id,
        'qty' => random_int(2, 6),
    ];
});
