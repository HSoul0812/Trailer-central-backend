<?php
declare(strict_types=1);

/** @var Factory $factory */

use App\Models\Website\User\WebsiteUserToken;
use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;

$factory->define(WebsiteUserToken::class, static function (Faker $faker): array {
    return [
        'access_token' => $faker->uuid
    ];
});

